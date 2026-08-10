<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'book_date';
    public $sortDirection = 'desc';

    public $editingBookId = null;
    public $market_id;
    public $employee_id;
    public $book_date;
    public $status = 'draft';

    // Generator properties
    public $generateStartDate = '';
    public $generateEndDate = '';

    public $filterMonth;
    public $search = '';

    public function mount()
    {
        abort_unless(auth()->user() && auth()->user()->hasAnyPermission(['order_books:read', 'order_books:read-self']), 403, 'Unauthorized.');
        $this->filterMonth = 'all';
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'market_id' => [
                'required',
                'exists:markets,id',
                \Illuminate\Validation\Rule::unique('order_books', 'market_id')
                    ->where(fn ($query) => $query->where('book_date', $this->book_date))
                    ->ignore($this->editingBookId)
            ],
            'employee_id' => 'required|exists:employees,id',
            'book_date' => 'required|date',
            'status' => 'required|in:draft,locked_for_delivery,completed',
        ];
    }

    public function messages()
    {
        return [
            'market_id.unique' => 'Pasar ini sudah memiliki buku order di tanggal yang dipilih.',
        ];
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->editingBookId = null;
        $this->market_id = null;
        $this->book_date = now()->addDay()->format('Y-m-d');
        $this->status = 'draft';
        
        if (auth()->user() && auth()->user()->employee) {
            $this->employee_id = auth()->user()->employee->id;
        } else {
            $this->employee_id = null;
        }
        
        $this->modal('create-book-modal')->show();
    }

    public function editBook($id)
    {
        $this->resetValidation();
        $book = OrderBook::findOrFail($id);
        
        $this->editingBookId = $book->id;
        $this->market_id = $book->market_id;
        $this->employee_id = $book->employee_id;
        $this->book_date = $book->book_date->format('Y-m-d');
        $this->status = $book->status;
        
        $this->status = $book->status;
        
        $this->modal('create-book-modal')->show();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingBookId) {
            $book = OrderBook::findOrFail($this->editingBookId);
            $book->update([
                'market_id' => $this->market_id,
                'employee_id' => $this->employee_id,
                'book_date' => $this->book_date,
                'status' => $this->status,
            ]);
            $message = 'Buku Order berhasil diperbarui.';
            $redirect = false;
        } else {
            $book = OrderBook::create([
                'market_id' => $this->market_id,
                'employee_id' => $this->employee_id,
                'book_date' => $this->book_date,
                'status' => 'draft',
            ]);
            $message = 'Buku Order berhasil dibuat.';
            $redirect = true;
        }

        $this->modal('create-book-modal')->close();
        Flux::toast(heading: 'Success', text: $message, variant: 'success');
        
        if ($redirect) {
            return $this->redirect(route('order-books.show', $book->id), navigate: true);
        }
    }

    public function openGenerateModal()
    {
        $this->generateStartDate = now()->format('Y-m-d');
        $this->generateEndDate = now()->addDays(6)->format('Y-m-d'); // 1 minggu
        $this->modal('generate-books-modal')->show();
    }

    public function generateFromSchedule()
    {
        $this->validate([
            'generateStartDate' => 'required|date',
            'generateEndDate' => 'required|date|after_or_equal:generateStartDate',
        ]);

        $startDate = \Carbon\Carbon::parse($this->generateStartDate);
        $endDate = \Carbon\Carbon::parse($this->generateEndDate);
        
        $schedules = \App\Models\SalesSchedule::all();
        $generatedCount = 0;

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Check libur jumat (Jumat = 5 in Carbon dayOfWeekIso, 5 is Friday. Wait, Carbon->dayOfWeek 0=Sun, 1=Mon, 5=Fri).
            // Atau tanggal merah (bisa dikembangkan nanti pake API/table libur, tapi sementara bypass Jumat dulu)
            if ($date->dayOfWeek === \Carbon\Carbon::FRIDAY) {
                continue;
            }

            // Get schedules for this dayOfWeek (0=Minggu, 1=Senin, ..., 6=Sabtu)
            $dayOfWeek = $date->dayOfWeek;
            
            $daySchedules = $schedules->where('day_of_week', $dayOfWeek);
            
            foreach ($daySchedules as $sch) {
                // Check if exists
                $exists = OrderBook::where('book_date', $date->format('Y-m-d'))
                    ->where('market_id', $sch->market_id)
                    ->exists();

                if (!$exists) {
                    OrderBook::create([
                        'employee_id' => $sch->employee_id,
                        'market_id' => $sch->market_id,
                        'book_date' => $date->format('Y-m-d'),
                        'status' => 'draft',
                    ]);
                    $generatedCount++;
                }
            }
        }

        $this->modal('generate-books-modal')->close();
        
        if ($generatedCount > 0) {
            Flux::toast(heading: 'Success', text: "$generatedCount Buku Order berhasil dibuat.", variant: 'success');
        } else {
            Flux::toast(heading: 'Info', text: 'Tidak ada Buku Order baru yang dibuat (sudah ada semua).', variant: 'info');
        }
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($id)
    {
        $orderBook = OrderBook::findOrFail($id);
        $orderBook->delete();

        Flux::toast(heading: 'Success', text: 'Buku Order berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function orderBooks()
    {
        $query = OrderBook::with(['market', 'employee'])
            ->withCount('orders')
            ->orderBy($this->sortBy, $this->sortDirection);

        if (auth()->user() && auth()->user()->hasPermissionTo('order_books:read-self') && !auth()->user()->hasPermissionTo('order_books:read')) {
            $query->where('employee_id', auth()->user()->employee->id ?? 0);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('market', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                })->orWhereHas('employee', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('status', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterMonth && $this->filterMonth !== 'all') {
            $parts = explode('-', $this->filterMonth);
            if (count($parts) === 2) {
                $query->whereYear('book_date', $parts[0])
                      ->whereMonth('book_date', $parts[1]);
            }
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function availableMonths()
    {
        $oldest = OrderBook::min('book_date');
        $latest = OrderBook::max('book_date');
        
        $start = $oldest ? \Carbon\Carbon::parse($oldest)->startOfMonth() : now()->startOfMonth();
        $end = $latest ? \Carbon\Carbon::parse($latest)->startOfMonth() : now()->startOfMonth();
        
        if (now()->startOfMonth()->lt($start)) $start = now()->startOfMonth();
        if (now()->startOfMonth()->gt($end)) $end = now()->startOfMonth();
        
        $months = [];
        $current = $end->copy();
        while ($current->gte($start)) {
            $months[$current->format('Y-m')] = $current->translatedFormat('F Y');
            $current = $current->subMonth();
        }
        
        return $months;
    }

    public function render()
    {
        return view('livewire.order-books.index', [
            'markets' => \App\Models\Market::all(),
            'employees' => \App\Models\Employee::all(),
        ]);
    }
}
