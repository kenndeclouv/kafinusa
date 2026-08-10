{{--
  x-chart — pure SVG, zero dependencies. Supports single and multi-series.

  Single-series:
    <x-chart :data="[['Jan',120],['Feb',180]]" label="Visitors" />
    <x-chart :data="$stats" type="bar" color="#5865f2" label="Members" />

  Multi-series:
    <x-chart :series="[
        ['label'=>'Instagram','color'=>'#10b981','data'=>[['2021',30],['2022',35],['2023',60]]],
        ['label'=>'Twitter',  'color'=>'#3b82f6','data'=>[['2021',25],['2022',20],['2023',22]]],
        ['label'=>'Facebook', 'color'=>'#f43f5e','data'=>[['2021',22],['2022',15],['2023',8]]],
    ]" type="line" />

  Props:
    data    [[label,value],...] or [label=>value]   single-series data
    series  array of {label,data,color?}            multi-series (overrides data)
    label   series name (single-series)             default: 'Value'
    type    area | line | bar                       default: 'area'
    height  px                                     default: 240
    color   CSS color (single-series)              default: var(--color-accent)
    smooth  bezier curves                          default: true
    legend  show legend row                        default: true
--}}
@props([
    'data' => [],
    'series' => null,
    'label' => 'Value',
    'type' => 'area',
    'height' => 240,
    'color' => null,
    'smooth' => true,
    'legend' => true,
])

@php
    $defaultColors = ['#10b981', '#3b82f6', '#f43f5e', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899'];

    // Normalize into unified series array
    $normalized = [];
    if ($series !== null) {
        foreach ($series as $idx => $s) {
            $rows = [];
            $raw = is_array($s) && isset($s['data']) ? $s['data'] : [];
            foreach ($raw as $k => $v) {
                if (is_array($v) && array_key_exists(0, $v)) {
                    $rows[] = [(string) $v[0], (float) ($v[1] ?? 0)];
                } elseif (is_array($v) && isset($v['label'])) {
                    $rows[] = [(string) $v['label'], (float) ($v['value'] ?? 0)];
                } else {
                    $rows[] = [(string) $k, (float) $v];
                }
            }
            $normalized[] = [
                'label' => (string) ($s['label'] ?? 'Series ' . ($idx + 1)),
                'data' => $rows,
                'color' => (string) ($s['color'] ?? $defaultColors[$idx % count($defaultColors)]),
            ];
        }
    } else {
        $rows = [];
        foreach ($data as $k => $v) {
            if (is_array($v) && array_key_exists('label', $v)) {
                $rows[] = [(string) $v['label'], (float) ($v['value'] ?? 0)];
            } elseif (is_array($v) && array_key_exists(0, $v)) {
                $rows[] = [(string) $v[0], (float) ($v[1] ?? 0)];
            } else {
                $rows[] = [(string) $k, (float) $v];
            }
        }
        $normalized[] = [
            'label' => $label,
            'data' => $rows,
            'color' => $color ?? 'var(--color-accent)',
        ];
    }

    $uid = 'cg' . substr(md5(uniqid((string) rand(), true)), 0, 8);
    $seriesJson = json_encode($normalized);
    $typeJs = json_encode($type);
    $smoothJs = $smooth ? 'true' : 'false';
    $heightPx = (int) $height;
    $showLegend = $legend && count($normalized) > 1 ? 'true' : 'false';
@endphp

{{-- x-data uses SINGLE-QUOTE wrapper so JS can freely use double-quotes inside --}}
<div x-data='{
    series:     {{ $seriesJson }},
    type:       {{ $typeJs }},
    smooth:     {{ $smoothJs }},
    height:     {{ $heightPx }},
    showLegend: {{ $showLegend }},

    svgW: 600,
    padL: 44, padR: 12, padT: 12, padB: 32,

    get svgH()   { return this.height; },
    get chartW() { return Math.max(1, this.svgW - this.padL - this.padR); },
    get chartH() { return Math.max(1, this.svgH - this.padT - this.padB); },

    hoverIdx: -1,

    /* ── global value range across all series ── */
    get allVals() { return this.series.flatMap(s => s.data.map(d => d[1])); },
    get _rawMin() { return this.allVals.length ? Math.min(...this.allVals) : 0; },
    get _rawMax() { return this.allVals.length ? Math.max(...this.allVals) : 1; },
    get vMin() {
        if (this.type === "bar") return 0;
        const lo = this._rawMin, hi = this._rawMax;
        return lo === hi ? Math.max(0, lo - 1) : Math.max(0, lo - (hi - lo) * 0.05);
    },
    get vMax() {
        const hi = this._rawMax, lo = this.vMin;
        return hi === lo ? hi + 1 : hi + (hi - lo) * 0.08;
    },

    /* ── coordinate helpers ── */
    get xData() { return this.series[0] ? this.series[0].data : []; },
    get N()      { return this.xData.length; },
    toX(i) {
        if (this.N === 0) return 0;
        if (this.type === "bar") {
            return this.padL + ((i + 0.5) / this.N) * this.chartW;
        }
        if (this.N <= 1) return this.padL + this.chartW / 2;
        return this.padL + (i / (this.N - 1)) * this.chartW;
    },
    toY(v) {
        const range = this.vMax - this.vMin || 1;
        return this.padT + this.chartH - ((v - this.vMin) / range) * this.chartH;
    },
    ptsFor(s) { return s.data.map((d, i) => ({ x: this.toX(i), y: this.toY(d[1]) })); },

    /* ── formatter ── */
    fmt(v) {
        if (Math.abs(v) >= 1e6) return (v/1e6).toFixed(1).replace(/\.0$/,"") + "M";
        if (Math.abs(v) >= 1e3) return (v/1e3).toFixed(1).replace(/\.0$/,"") + "k";
        return v % 1 === 0 ? String(Math.round(v)) : v.toFixed(1);
    },

    /* ── path builders ── */
    _bezier(pts) {
        if (!pts.length) return "";
        if (pts.length === 1) return "M " + pts[0].x.toFixed(1) + " " + pts[0].y.toFixed(1);
        
        // Monotone Cubic Interpolation to prevent overshooting while maintaining smooth curves
        let m = [];
        for (let i = 0; i < pts.length; i++) {
            if (i === 0) m.push(pts[1].y - pts[0].y);
            else if (i === pts.length - 1) m.push(pts[i].y - pts[i-1].y);
            else m.push((pts[i+1].y - pts[i-1].y) / 2);
        }
        
        for (let i = 0; i < pts.length - 1; i++) {
            let dy = pts[i+1].y - pts[i].y;
            if (dy === 0) {
                m[i] = 0; m[i+1] = 0;
            } else {
                if (Math.sign(m[i]) !== Math.sign(dy)) m[i] = 0;
                if (Math.sign(m[i+1]) !== Math.sign(dy)) m[i+1] = 0;
            }
        }

        let d = "M " + pts[0].x.toFixed(1) + " " + pts[0].y.toFixed(1);
        for (let i = 0; i < pts.length - 1; i++) {
            const p1 = pts[i], p2 = pts[i+1];
            const dx = p2.x - p1.x;
            const cp1x = p1.x + dx / 3;
            const cp1y = p1.y + m[i] / 3;
            const cp2x = p2.x - dx / 3;
            const cp2y = p2.y - m[i+1] / 3;

            d += " C " + cp1x.toFixed(1) + " " + cp1y.toFixed(1)
               + " " + cp2x.toFixed(1) + " " + cp2y.toFixed(1)
               + " " + p2.x.toFixed(1) + " " + p2.y.toFixed(1);
        }
        return d;
    },
    _poly(pts) {
        return pts.length ? "M " + pts.map(p => p.x.toFixed(1) + " " + p.y.toFixed(1)).join(" L ") : "";
    },
    pathFor(pts) { return this.smooth ? this._bezier(pts) : this._poly(pts); },

    /* ── Y-axis ticks ── */
    get yTicks() {
        if (this.chartH <= 0) return [];
        const range = this.vMax - this.vMin;
        if (range === 0) return [this.vMin];
        const count = Math.max(2, Math.floor(this.chartH / 55));
        const raw = range / (count - 1);
        const mag = Math.pow(10, Math.floor(Math.log10(raw)));
        const nice = ([1,2,2.5,5,10].map(x => x*mag).find(s => s>=raw)) || mag*10;
        const ticks = [];
        for (let v = Math.floor(this.vMin/nice)*nice; v <= this.vMax+nice*0.01; v += nice) {
            const r = Math.round(v*1e9)/1e9;
            if (r >= this.vMin-nice*0.01 && r <= this.vMax+nice*0.01) ticks.push(r);
        }
        return ticks;
    },
    get xLabels() {
        const n = this.N;
        if (!n || !this.svgW) return [];
        const maxL = Math.max(2, Math.floor(this.chartW / 75));
        const step = Math.max(1, Math.ceil((n-1) / (maxL-1)));
        const result = [];
        for (let i = 0; i < n; i++)
            if (i === 0 || i === n-1 || i % step === 0)
                result.push({ label: this.xData[i][0], x: this.toX(i) });
        return result;
    },

    /* ── SVG innerHTML helpers (single-quote attr wrapper ⟹ double-quotes safe inside) ── */
    get yAxisHtml() {
        if (!this.svgW) return "";
        return this.yTicks.map(tick => {
            const y  = this.toY(tick).toFixed(1);
            const ty = (this.toY(tick) + 4).toFixed(1);
            return "<line x1=\"" + this.padL + "\" y1=\"" + y + "\" x2=\"" + (this.padL+this.chartW) + "\" y2=\"" + y + "\" stroke=\"currentColor\" stroke-opacity=\"0.06\" stroke-width=\"1\"/>"
                 + "<text x=\"" + (this.padL-8) + "\" y=\"" + ty + "\" text-anchor=\"end\" fill=\"currentColor\" fill-opacity=\"0.4\" font-size=\"11\" font-family=\"inherit\">" + this.fmt(tick) + "</text>";
        }).join("");
    },
    get xAxisHtml() {
        if (!this.svgW) return "";
        const baseY = this.padT + this.chartH + 20;
        return this.xLabels.map(lbl =>
            "<text x=\"" + lbl.x.toFixed(1) + "\" y=\"" + baseY + "\" text-anchor=\"middle\" fill=\"currentColor\" fill-opacity=\"0.4\" font-size=\"11\" font-family=\"inherit\">" + lbl.label + "</text>"
        ).join("");
    },

    /* ── line / area paths for all series ── */
    get seriesHtml() {
        if (!this.svgW) return "";
        let html = "";
        for (const s of this.series) {
            const pts  = this.ptsFor(s);
            const c    = s.color;
            const line = this.pathFor(pts);
            if ((this.type === "area") && pts.length > 1) {
                const base = this.toY(this.vMin).toFixed(1);
                const area = line + " L " + pts[pts.length-1].x.toFixed(1) + " " + base
                           + " L " + pts[0].x.toFixed(1) + " " + base + " Z";
                html += "<path d=\"" + area + "\" style=\"fill:" + c + ";fill-opacity:0.1\" stroke=\"none\"/>";
            }
            if (this.type === "area" || this.type === "line")
                html += "<path d=\"" + line + "\" fill=\"none\" style=\"stroke:" + c + ";stroke-width:2;stroke-linecap:round;stroke-linejoin:round\"/>";
        }
        return html;
    },

    /* ── grouped bars for all series ── */
    get barsHtml() {
        if (this.type !== "bar" || !this.svgW) return "";
        const nS     = this.series.length;
        const gap    = this.N > 1 ? this.toX(1) - this.toX(0) : this.chartW;
        const groupW = gap * 0.72;
        const barW   = groupW / nS;
        const baseY  = this.toY(this.vMin);
        let html = "";
        this.series.forEach((s, si) => {
            s.data.forEach((d, i) => {
                const cx  = this.toX(i);
                const x   = (cx - groupW/2 + si*barW).toFixed(1);
                const y   = this.toY(d[1]).toFixed(1);
                const h   = Math.max(0, baseY - this.toY(d[1])).toFixed(1);
                const op  = this.hoverIdx < 0 || this.hoverIdx === i ? 1 : 0.3;
                html += "<rect x=\"" + x + "\" y=\"" + y + "\" width=\"" + barW.toFixed(1) + "\" height=\"" + h + "\" rx=\"2\" style=\"fill:" + s.color + ";fill-opacity:" + op + "\"/>";
            });
        });
        return html;
    },

    /* ── hover dots for all series ── */
    get dotsHtml() {
        if (this.hoverIdx < 0 || this.type === "bar" || !this.svgW) return "";
        let html = "";
        for (const s of this.series) {
            const pts = this.ptsFor(s);
            const pt  = pts[this.hoverIdx];
            if (!pt) continue;
            html += "<circle cx=\"" + pt.x.toFixed(1) + "\" cy=\"" + pt.y.toFixed(1) + "\" r=\"4.5\" style=\"fill:" + s.color + ";stroke:white;stroke-width:2\"/>";
        }
        return html;
    },

    /* ── tooltip ── */
    get tooltip() {
        if (this.hoverIdx < 0 || !this.xData[this.hoverIdx]) return null;
        const i    = this.hoverIdx;
        const lbl  = this.xData[i][0];
        const rows = this.series.map(s => ({ label: s.label, val: this.fmt(s.data[i] ? s.data[i][1] : 0), color: s.color }));
        const firstPts = this.ptsFor(this.series[0]);
        const px   = firstPts[i] ? firstPts[i].x : this.toX(i);
        const left = px > this.svgW / 2 ? px - 152 : px + 10;
        return { lbl, rows, left, top: this.padT };
    },

    /* ── mouse / resize ── */
    onMove(e) {
        const rect = this.$refs.svg.getBoundingClientRect();
        const relX = e.clientX - rect.left;
        if (relX < this.padL || relX > this.padL + this.chartW) { this.hoverIdx = -1; return; }
        let best = 0, minD = Infinity;
        for (let i = 0; i < this.N; i++) {
            const d = Math.abs(this.toX(i) - relX);
            if (d < minD) { minD = d; best = i; }
        }
        this.hoverIdx = best;
    },
    init() {
        const upd = () => { 
            if (!this.$el) return;
            const w = this.$el.getBoundingClientRect().width;
            if (w > 0) this.svgW = w; 
        };
        upd();
        this._ro = new ResizeObserver(upd);
        this._ro.observe(this.$el);
    },
    destroy() {
        if (this._ro) this._ro.disconnect();
    }
}'
    class="relative w-full {{ $attributes->get('class', '') }}">

    {{-- ──────────────────────── SVG ──────────────────────────────────── --}}
    <svg x-ref="svg" class="w-full overflow-visible" :style="'height:' + height + 'px'" @mousemove="onMove($event)"
        @mouseleave="hoverIdx = -1">

        {{-- Y-axis grid + labels --}}
        <g x-effect="$el.innerHTML = yAxisHtml"></g>

        {{-- X-axis labels --}}
        <g x-effect="$el.innerHTML = xAxisHtml"></g>

        {{-- Lines / areas --}}
        <g x-effect="$el.innerHTML = seriesHtml"></g>

        {{-- Bars --}}
        <g x-effect="$el.innerHTML = barsHtml"></g>

        {{-- Hover crosshair --}}
        <line x-show="hoverIdx >= 0"
            :x1="hoverIdx >= 0 && ptsFor(series[0])[hoverIdx] ? ptsFor(series[0])[hoverIdx].x.toFixed(1) : 0"
            :y1="padT"
            :x2="hoverIdx >= 0 && ptsFor(series[0])[hoverIdx] ? ptsFor(series[0])[hoverIdx].x.toFixed(1) : 0"
            :y2="padT + chartH" stroke="currentColor" stroke-opacity="0.22" stroke-width="1"
            stroke-dasharray="4 3" />

        {{-- Hover dots (one per series) --}}
        <g x-effect="$el.innerHTML = dotsHtml"></g>

    </svg>

    {{-- ──────────────────────── Tooltip (HTML namespace — x-if is safe) ── --}}
    <template x-if="tooltip">
        <div class="absolute z-50 pointer-events-none"
            :style="'left:' + tooltip.left + 'px;top:' + (tooltip.top + 4) + 'px;min-width:140px;max-width:180px'">
            <div
                class="rounded-xl shadow-xl px-3 py-2.5
                        bg-white/40 dark:bg-zinc-900/60
                        backdrop-blur-sm">
                <p class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 mb-2 truncate"
                    x-text="tooltip.lbl"></p>
                <div class="space-y-1.5">
                    <template x-for="row in tooltip.rows" :key="row.label">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0" :style="'background:' + row.color"></span>
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate"
                                    x-text="row.label"></span>
                            </div>
                            <span class="text-sm font-bold text-zinc-800 dark:text-white tabular-nums shrink-0"
                                x-text="row.val"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- ──────────────────────── Legend ────────────────────────────────── --}}
    <template x-if="showLegend">
        <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5 mt-3">
            <template x-for="s in series" :key="s.label">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background:' + s.color"></span>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400" x-text="s.label"></span>
                </div>
            </template>
        </div>
    </template>

</div>
