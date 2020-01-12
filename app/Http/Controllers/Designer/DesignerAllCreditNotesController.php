<?php

namespace App\Http\Controllers\Designer;

use App\CreditNoteItem;
use App\Currency;
use App\Helper\Reply;
use App\Http\Requests\CreditNotes\creditNoteFileStore;
use App\Http\Requests\CreditNotes\StoreCreditNotes;
use App\Http\Requests\CreditNotes\UpdateCreditNote;
use App\CreditNotes;
use App\Invoice;
use App\InvoiceSetting;
use App\Product;
use App\Project;
use App\Setting;
use App\Tax;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class DesignerAllCreditNotesController extends DesignerBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.credit-note');
        $this->pageIcon = 'ti-receipt';
        $this->middleware(function ($request, $next) {
            if (!in_array('invoices', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        if(!$this->user->can('view_invoices')){
            abort(403);
        }
        $this->projects = Project::all();
        return view('designer.credit-notes.index', $this->data);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function data(Request $request)
    {
        $firstCreditNotes = CreditNotes::orderBy('id', 'desc')->first();
        $creditNotes = CreditNotes::with(['project:id,project_name,client_id', 'currency:id,currency_symbol,currency_code', 'invoice'])
            ->select('credit_notes.id', 'credit_notes.project_id', 'credit_notes.invoice_id', 'credit_notes.currency_id', 'credit_notes.cn_number', 'credit_notes.total', 'credit_notes.issue_date');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $creditNotes = $creditNotes->where(DB::raw('DATE(credit_notes.`issue_date`)'), '>=', $request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $creditNotes = $creditNotes->where(DB::raw('DATE(credit_notes.`issue_date`)'), '<=', $request->endDate);
        }

        if ($request->projectID != 'all' && !is_null($request->projectID)) {
            $creditNotes = $creditNotes->where('credit_notes.project_id', '=', $request->projectID);
        }

        $creditNotes = $creditNotes->orderBy('credit_notes.id', 'desc')->get();

        return DataTables::of($creditNotes)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use($firstCreditNotes){
                $action = '<div class="btn-group m-r-10">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-info btn-outline  dropdown-toggle waves-effect waves-light" type="button">Action <span class="caret"></span></button>
                <ul role="menu" class="dropdown-menu">';

                if ($this->user->can('view_invoices')) {
                    $action .= '<li><a href="' . route("designer.all-credit-notes.download", $row->id) . '"><i class="fa fa-download"></i> Download</a></li>';
                }

                if ($this->user->can('edit_invoices')) {
                    $action .= '<li><a href="' . route("designer.all-credit-notes.edit", $row->id) . '"><i class="fa fa-pencil"></i> Edit</a></li>';
                }

                if($this->user->can('delete_invoices') && $firstCreditNotes->id == $row->id){
                    $action .= '<li><a href="javascript:;" data-toggle="tooltip"  data-credit-notes-id="' . $row->id . '" class="sa-params"><i class="fa fa-times"></i> Delete</a></li>';
                }
                $action .= '</ul>
              </div>
              ';

                return $action;
            })
            ->editColumn('project_name', function ($row) {
                return '<a href="' . route('designer.projects.show', $row->project_id) . '">' . ucfirst($row->project->project_name) . '</a>';
            })
            ->editColumn('cn_number', function ($row) {
                return '<a href="' . route('designer.all-credit-notes.show', $row->id) . '">' . ucfirst($row->cn_number) . '</a>';
            })
            ->editColumn('invoice_number', function ($row) {
                return $row->invoice ? ucfirst($row->invoice->invoice_number) : '--';
            })
            ->editColumn('total', function ($row) {
                return $row->currency->currency_symbol . $row->total . ' (' . $row->currency->currency_code . ')';
            })
            ->editColumn(
                'issue_date',
                function ($row) {
                    return $row->issue_date->timezone($this->global->timezone)->format($this->global->date_format);
                }
            )
            ->rawColumns(['project_name', 'action', 'cn_number', 'invoice_number'])
            ->removeColumn('currency_symbol')
            ->removeColumn('currency_code')
            ->removeColumn('project_id')
            ->make(true);
    }

    public function download($id)
    {
        //        header('Content-type: application/pdf');

        $this->creditNote = CreditNotes::findOrFail($id);
        $this->invoiceNumber = 0;
        if (Invoice::where('id', '=', $this->creditNote->invoice_id)->exists()) {
            $this->invoiceNumber = Invoice::select('invoice_number')->where('id', $this->creditNote->invoice_id)->first();
        }
        // Download file uploaded
        if ($this->creditNote->file != null) {
            return response()->download(storage_path('app/public/credit-note-files') . '/' . $this->creditNote->file);
        }

        if ($this->creditNote->discount > 0) {
            if ($this->creditNote->discount_type == 'percent') {
                $this->creditNote = (($this->creditNote->discount / 100) * $this->creditNote->sub_total);
            } else {
                $this->discount = $this->creditNote->discount;
            }
        } else {
            $this->discount = 0;
        }

        $taxList = array();

        $items = CreditNoteItem::whereNotNull('tax_id')
            ->where('credit_note_id', $this->creditNote->id)
            ->get();

        foreach ($items as $item) {
            if (!isset($taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'])) {
                $taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'] = ($item->tax->rate_percent / 100) * $item->amount;
            } else {
                $taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'] = $taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'] + (($item->tax->rate_percent / 100) * $item->amount);
            }
        }

        $this->taxes = $taxList;

        $this->settings = Setting::first();

        $this->creditNoteSetting = InvoiceSetting::first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('credit-notes.' . $this->creditNoteSetting->template, $this->data);
        $filename = $this->creditNote->cn_number;
        //       return $pdf->stream();
        return $pdf->download($filename . '.pdf');
    }

    public function destroy($id)
    {
        $firstCreditNote = CreditNotes::orderBy('id', 'desc')->first();

        if ($firstCreditNote->id == $id)
        {
            $creditNote = CreditNotes::find($id);
            if (Invoice::where('id', '=', $creditNote->invoice_id)->exists()) {
                Invoice::where('id', '=', $creditNote->invoice_id)->update(['credit_note' => 0]);
            }
            CreditNotes::destroy($id);
            return Reply::success(__('messages.creditNoteDeleted'));
        }
        else{
            return Reply::error(__('messages.creditNoteCanNotDeleted'));
        }
    }

    public function create()
    {
        abort(404);
    }

    public function store(StoreCreditNotes $request)
    {
        $items = $request->input('item_name');
        $cost_per_item = $request->input('cost_per_item');
        $quantity = $request->input('quantity');
        $amount = $request->input('amount');
        $tax = $request->input('taxes');

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && (intval($qty) < 1)) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        $creditNote = new CreditNotes();
        $creditNote->project_id = $request->project_id;
        $creditNote->cn_number = CreditNotes::count()+1;
        $creditNote->invoice_id = $request->invoice_id ? $request->invoice_id : null;
        $creditNote->issue_date = Carbon::parse($request->issue_date)->format('Y-m-d');
        $creditNote->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $creditNote->sub_total = round($request->sub_total, 2);
        $creditNote->discount = round($request->discount_value, 2);
        $creditNote->discount_type = $request->discount_type;
        $creditNote->total = round($request->total, 2);
        $creditNote->currency_id = $request->currency_id;
        $creditNote->recurring = $request->recurring_payment;
        $creditNote->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        $creditNote->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        $creditNote->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        $creditNote->note = $request->note;
        $creditNote->save();

        foreach ($items as $key => $item) :
            if (!is_null($item)) {
                CreditNoteItem::create(['credit_note_id' => $creditNote->id, 'item_name' => $item, 'type' => 'item', 'quantity' => $quantity[$key], 'unit_price' => round($cost_per_item[$key], 2), 'amount' => round($amount[$key], 2), 'tax_id' => $tax[$key]]);
            }
        endforeach;

        if ($request->invoice_id){
            $invoice = Invoice::findOrFail($request->invoice_id);
            $invoice->credit_note = 1;
            $invoice->save();
        }

        //log search
        $this->logSearchEntry($creditNote->id, 'CreditNote ' . $creditNote->cn_number, 'designer.all-credit-notes.show');

        return Reply::redirect(route('designer.all-credit-notes.index'), __('messages.creditNoteCreated'));
    }

    public function edit($id)
    {
        if(!$this->user->can('edit_invoices')){
            abort(403);
        }
        $this->creditNote = CreditNotes::findOrFail($id);
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        $this->taxes = Tax::all();
        $this->products = Product::all();

        return view('designer.credit-notes.edit', $this->data);
    }

    public function update(UpdateCreditNote $request, $id)
    {
        $items = $request->input('item_name');
        $cost_per_item = $request->input('cost_per_item');
        $quantity = $request->input('quantity');
        $amount = $request->input('amount');
        $tax = $request->input('taxes');

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && $qty < 1) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        $creditNote = CreditNotes::findOrFail($id);

        $creditNote->project_id = $request->project_id;
        $creditNote->issue_date = Carbon::parse($request->issue_date)->format('Y-m-d');
        $creditNote->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $creditNote->sub_total = round($request->sub_total, 2);
        $creditNote->discount = round($request->discount_value, 2);
        $creditNote->discount_type = $request->discount_type;
        $creditNote->total = round($request->total, 2);
        $creditNote->currency_id = $request->currency_id;
        $creditNote->recurring = $request->recurring_payment;
        $creditNote->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        $creditNote->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        $creditNote->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        $creditNote->note = $request->note;
        $creditNote->save();

        // delete and create new
        CreditNoteItem::where('credit_note_id', $creditNote->id)->delete();

        foreach ($items as $key => $item) :
            CreditNoteItem::create(['credit_note_id' => $creditNote->id, 'item_name' => $item, 'type' => 'item', 'quantity' => $quantity[$key], 'unit_price' => round($cost_per_item[$key], 2), 'amount' => round($amount[$key], 2), 'tax_id' => $tax[$key]]);
        endforeach;

        return Reply::redirect(route('designer.all-credit-notes.index'), __('messages.creditNoteUpdated'));
    }

    public function show($id)
    {
        $this->creditNote = CreditNotes::findOrFail($id);
        $this->paidAmount = $this->creditNote->getPaidAmount();

        if ($this->creditNote->discount > 0) {
            if ($this->creditNote->discount_type == 'percent') {
                $this->discount = (($this->creditNote->discount / 100) * $this->creditNote->sub_total);
            } else {
                $this->discount = $this->creditNote->discount;
            }
        } else {
            $this->discount = 0;
        }
        $this->invoiceExist = false;
        if (Invoice::where('id', '=', $this->creditNote->invoice_id)->exists()) {
            $this->invoiceExist = true;
        }

        $taxList = array();

        $items = CreditNoteItem::whereNotNull('tax_id')
            ->where('credit_note_id', $this->creditNote->id)
            ->get();

        foreach ($items as $item) {
            if (!isset($taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'])) {
                $taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'] = ($item->tax->rate_percent / 100) * $item->amount;
            } else {
                $taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'] = $taxList[$item->tax->tax_name . ': ' . $item->tax->rate_percent . '%'] + (($item->tax->rate_percent / 100) * $item->amount);
            }
        }

        $this->taxes = $taxList;

        $this->settings = Setting::first();
        $this->creditNoteSetting = InvoiceSetting::first();
        return view('designer.credit-notes.show', $this->data);
    }

    public function convertInvoice($id)
    {
        $this->invoiceId = $id;
        $this->creditNote = Invoice::with('items')->findOrFail($id);
        $this->lastCreditNote = CreditNotes::count()+1;
        $this->creditNoteSetting = InvoiceSetting::first();
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        $this->taxes = Tax::all();
        $this->products = Product::all();
        $this->zero = '';
        if (strlen($this->lastCreditNote) < $this->creditNoteSetting->credit_note_digit){
            for ($i=0; $i<$this->creditNoteSetting->credit_note_digit-strlen($this->lastCreditNote); $i++){
                $this->zero = '0'.$this->zero;
            }
        }

        $discount = $this->creditNote->items->filter(function ($value, $key) {
            return $value->type == 'discount';
        });

        $tax = $this->creditNote->items->filter(function ($value, $key) {
            return $value->type == 'tax';
        });

        $this->totalTax = $tax->sum('amount');
        $this->totalDiscount = $discount->sum('amount');

        return view('designer.credit-notes.convert_invoice', $this->data);
    }

    public function addItems(Request $request)
    {
        $this->items = Product::with('tax')->find($request->id);
        $this->taxes = Tax::all();
        $view = view('designer.credit-notes.add-item', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }


    public function paymentDetail($creditNoteID)
    {
        $this->creditNote = CreditNotes::findOrFail($creditNoteID);

        return View::make('designer.credit-notes.payment-detail', $this->data);
    }

    /**
     * @param InvoiceFileStore $request
     * @return array
     */
    public function storeFile(creditNoteFileStore $request)
    {
//        dd($request->all());
        $creditNoteId = $request->credit_note_id;
        $file = $request->file('file');

        $newName = $file->hashName(); // setting hashName name
        // Getting invoice data
        $creditNote = CreditNotes::find($creditNoteId);

        if ($creditNote != null) {

            if ($creditNote->file != null) {
                unlink(storage_path('app/public/credit-note-files') . '/' . $creditNote->file);
            }

            $file->move(storage_path('app/public/credit-note-files'), $newName);

            $creditNote->file = $newName;
            $creditNote->file_original_name = $file->getClientOriginalName(); // Getting uploading file name;

            $creditNote->save();

            return Reply::success(__('messages.fileUploadedSuccessfully'));
        }

        return Reply::error(__('messages.fileUploadIssue'));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function destroyFile(Request $request)
    {
        $creditNoteId = $request->credit_note_id;

        $creditNote = CreditNotes::find($creditNoteId);

        if ($creditNote != null) {

            if ($creditNote->file != null) {
                unlink(storage_path('app/public/credit-note-files') . '/' . $creditNote->file);
            }

            $creditNote->file = null;
            $creditNote->file_original_name = null;

            $creditNote->save();
        }

        return Reply::success(__('messages.fileDeleted'));
    }
}
