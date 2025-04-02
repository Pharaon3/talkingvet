<?php

namespace App\Http\Livewire;

use App\Http\Controllers\NvoqNetworkController;
use App\Http\Livewire\models\trxColumn;
use App\Models\Nvoq\TransactionItemType;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Redirector;

class TransactionsTableComponent extends Component
{
    public $data;
    public $searchTerm = ''; // External-Id to search for
    public $sortColumn = '';
    public $sortDirection = 'asc';
    public $accounts = []; // user accessible nvoq accounts

    public $opts = [
        'account' => "",
        'startDate' => "",
        'endDate' => "",
        'itemType' => "", // todo find a way to fetch multiple types (popup shortcut,shortcut, dictations)
        'searchBy' => "external-id",
        'searchText' => "", // text to search for
        'resultLimit' => 0,
        'c' => "realUserName",
        'q' => "", // account username
    ];

    // to be sent with nvoq requests
    protected $allAccountsParams = [
        'startDate',
        'endDate',
        'itemType',
        'searchBy',
        'searchText',
        'resultLimit',
    ];

    protected $singleAccountParams = [
        'startDate',
        'endDate',
        'itemType',
        'searchBy',
        'searchText',
        'resultLimit',
        'c', // fields for query
        'q', // query
    ];

    protected $listeners = ['dateSelected', 'accountUpdated'];

    /**
     * The mount method is a special method in Livewire components that is called when the component
     * is first initialized. It's similar to the __construct method in traditional PHP classes,
     * but it's specific to Livewire components.
     *
     * In the context of a table component,
     * the mount method is typically used to initialize the table data.
     * This is where you would make any API calls or database queries to retrieve the data
     * that will be displayed in the table.
     * */
    public function mount($accounts)
    {
        // store allowed accounts
        $this->accounts = $accounts;

        // default values
        $this->opts['account'] = $accounts[0];
        $this->opts['q'] = $accounts[0];
        $this->opts['startDate'] = Carbon::today()->subWeeks(1)->format('m-d-Y H:i:s O');
        $this->opts['endDate'] = Carbon::tomorrow()->format('m-d-Y H:i:s O');
        $this->opts['resultLimit'] = 100;
        $this->opts['itemType'] = TransactionItemType::ANY; // any
        //dd($this->opts);


        $this->refresh(); // fetches data from nvoq
//        $this->sortBy($this->sortColumn); // sort at start
    }


    protected function getRules()
    {
        return [
            'opts.startDate' => ['required', 'date_format:m-d-Y H:i:s O'],
            'opts.endDate' => ['date_format:m-d-Y H:i:s O'],
            'opts.itemType' => [Rule::in([TransactionItemType::DICTATION, TransactionItemType::ANY, TransactionItemType::SHORTCUT, TransactionItemType::POPUP_SHORTCUT])],
            'opts.searchText' => ['string'],
            'opts.resultLimit' => ['numeric', 'min:5', 'max:3000'],
        ];
    }

    /**
     * only runs when calendar is closed, updates dates range and fetches new data from nvoq
     * @param $data array holds start,end dates
     * @return void
     */
    public function dateSelected($data)
    {
        // only update from nvoq if dates are changed
        if(
            $data['start'] != $this->opts['startDate'] ||
            $data['end'] != $this->opts['endDate']
        )
        {
            $this->opts['startDate'] = $data['start'];
            $this->opts['endDate'] = $data['end'];
            $this->refresh();
        }
        // otherwise do nothing
    }

    public function accountUpdated($updatedAccountName)
    {
        $oldAccount = $this->opts['account'];
        if($oldAccount != $updatedAccountName)
        {
            $this->opts['account'] = $updatedAccountName;
            $this->refresh();
        }
    }

    public function clearSearch()
    {
        $this->opts['searchText'] = "";
        $this->refresh();
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        usort($this->data, function ($a, $b) {
            if ($a[$this->sortColumn] === $b[$this->sortColumn]) {
                return 0;
            }

            if ($this->sortDirection === 'asc') {
                return $a[$this->sortColumn] < $b[$this->sortColumn] ? -1 : 1;
            } else {
                return $a[$this->sortColumn] > $b[$this->sortColumn] ? -1 : 1;
            }
        });
    }

    /*
     * Fetches data from nvoq servers obeying validated $opts
     */
    public function refresh()
    {
//        return;
//        $validator = \Validator::make($this->opts, $this->rules);

        $this->validate(); // throws if failed

        // update query with current account
        $this->opts['q'] = $this->opts['account'];

/*        dd($this->opts,
            Arr::only($this->opts,
                ($this->opts['account'] == __("dashboard.accounts.options.all") ?
                    $this->allAccountsParams
                    : $this->singleAccountParams
                )
            )
        );*/

        $response = NvoqNetworkController::GetTransactions(
            Arr::only($this->opts,
                ($this->opts['account'] == __("dashboard.accounts.options.all") ?
                    $this->allAccountsParams
                    : $this->singleAccountParams)
            )
        ); // Make API call to get data
//        dd($response, $response::class == Redirector::class);
        if ($response instanceof Redirector)
        {
            session()->flash("error", "Please login again");
            session()->flash('error', ["error" => "Please login again"]);
            return;
        }

        $transactions = collect($response)->map(function($trx){
            return (new Transaction())->fill($trx);
            //return Arr::only($trx, array_filter(Arr::pluck($this->columns(), 'from')));
        })->all();

//        dd($transactions[0], $transactions);

//        dd($response->isRedirection());
        /*dd(
            $response,
            $result,
            array_filter(Arr::pluck($this->columns(), 'from')),
        );*/

        $this->data = is_array($transactions) ? $transactions : [];
        session()->flash('trx-table', 'Last retrieved ' . count($this->data) . ' entries at ' . Carbon::now() );
    }


    public function review($trxId)
    {
//        (new ReviewController())->view((new ReviewRequest([], ['trx' => $trx])));
//        dd($trx);
//        session(['trx'=>$trx]);
//        redirect()->action(
//            'review.view', ['transactions' => Arr::pluck($this->data, 'reviewId'), 'selected'=>$trx]
//        );
//        redirect()->action('ReviewController@view');
        $transactions = Arr::pluck($this->data, 'id');
        $index = array_search($trxId, $transactions);
        // handle false $index
        session()->put(["review" => ['transactions' => Arr::pluck($this->data, 'id'), 'index'=>$index]]);
        redirect()->route('review.view');

        /*redirect()->route('review.view',
            ['transactions' => Arr::pluck($this->data, 'id'), 'index'=>$index]);*/

//        redirect()->route('review.view', ['transactions' => Arr::pluck($this->data, 'reviewId'), 'selected'=>$trx]);
//        $test = new ReviewController();
//        $test->internalRedirectShow(['transactions' => Arr::pluck($this->data, 'reviewId'), 'selected'=>$trx]);
//        dd($trx);
    }

    // UI: table columns
    public function columns(): array
    {
        return [
            trxColumn::make("Id", "reviewId")->sortable(),
            trxColumn::make("Date", "submitTime")->sortable(),
            trxColumn::make("Account", "realUserName"),
            trxColumn::make("Word Count", "wordCount")->sortable(),
            trxColumn::make("Audio Length", "audioLength")->sortable(),
            trxColumn::make("External ID", "externalId"),
            // trxColumn::make("Quality", "audioQuality"),
            trxColumn::make("type", "itemType")->hidden(),
            trxColumn::make("Actions"),
        ];
        /*"Id" => "review_id",
            "Date", "submit_time",
            "Account", "real_user_name",
            "Word Count", "word_count",
            "Audio Length", "audio_length",
            "External ID", "external_id",*/
    }

    public function render()
    {
        return view('livewire.transactions-table-component', [
            'data' => $this->data,
            'columns' => $this->columns(),
            'sortDirection' => $this->sortDirection
        ]);
    }
}
