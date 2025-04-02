<?php

namespace App\Http\Livewire;

use App\Http\Controllers\NvoqNetworkController;
use App\Models\CorrectionParameters;
use App\Models\Enums\CorrectionTypes;
use App\Models\Enums\ModalActions;
use App\Models\Transaction;
use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Factory\RendererFactory;
use Jfcherng\Diff\Renderer\RendererConstant;
use Livewire\Component;

class ReviewComponent extends Component
{
    public $activeTab = 'corrected';

    public $data = [
        'transactions' => [],
        'currentIndex' => 0,
        'trx' => [],
        'originalText' => "",
        'substitutedText' => "",
        'correctedText' => "",
        'orgDiff' => "",
        'subDiff' => "",
        'accuracy' => "?",
        'editing' => false,
        'modalOpen' => false,
        'modalData' => [
            'title' => "Info",
            'content' => ""
        ]
    ];

    protected $listeners = ['addToVocab'];

    public $modalAction = ModalActions::NONE;

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
    public function mount($transactions, $index)
    {
//        session()->flash("success", ['msg' => "yay"]);
//        session()->flash("error", ['hi' => "yay"]);
        // store allowed accounts

//        if(!session()->has('trx')) return;
//        $trx = session('trx');
        $this->data['transactions'] = $transactions; // Transactions IDs array
        $this->data['currentIndex'] = $index;
        $this->fetch($transactions[$index]); // ID
//        dd($index,$this->data,$transactions);
    }

    // fetches a transaction data from nvoq APIs using the transaction ID (reviewId)
    public function fetch($trxId)
    {
//        return;
        $response = NvoqNetworkController::GetSingleTransactionData(
            $trxId
        ); // Make API call to get data

//        dd($response);
        if($response == null || !is_array($response)) return; // abort

        $trx = new Transaction($response);
//        dd($trx, $response);
        $audio = NvoqNetworkController::GetAudio($trxId);

//        dd($audio);
        if($audio == null || !is_array($audio)) return;
        $trx['audio'] = $audio;
//        $this->emit('ReviewItemChanged', $audio);

        $this->data['editing'] = false;
        $this->data['originalText'] = $response['originalText'];
        $this->data['substitutedText'] = $response['substitutedText'];
//        $this->data['originalText'] = NvoqNetworkController::GetOriginalText($trx['id']);
//        $this->data['substitutedText'] = NvoqNetworkController::GetSubstitutedText($trx['id']);

        // if there's a status, fetch corrected text
//        if(!empty($trx['status']))
        if($trx['status'] == CorrectionTypes::CORRECTED->value)
        {
            // fetch corrected text
            $this->data['correctedText'] = $response['correctedText'];
            $this->data['accuracy'] = $this->calculateDiffPercentage($this->data['originalText'], $this->data['correctedText']); // accuracy
        }

        $this->data['trx'] = $trx;

//        dd($this->data, $trx);
//        session()->flash('trx-table', 'Last retrieved ' . count($this->data) . ' entries at ' . Carbon::now() );
    }

    public function next()
    {
//        dd(1,$this->data, $this->modalAction);
        if($this->data['editing'])
        {
            $this->showModal(ModalActions::NEXT);
            return;
        }
//        dd(2,$this->data, $this->modalAction);
        $this->internalClear(); // close current
        // fetch next

        // not last item
        $currentIndex = $this->data['currentIndex'];
        $transactionsArr = $this->data['transactions'];
        $size = sizeof($transactionsArr);

        if($currentIndex < $size)
        {
            $currentIndex++;
            \Session::put('review.index', $currentIndex); // update session
            $this->data['currentIndex'] = $currentIndex;
            $this->fetch($transactionsArr[$currentIndex]); // ID
            $this->emit('ReviewItemChanged', $this->data['trx']['audio']);
        }
    }

    public function prev()
    {
        if($this->data['editing'])
        {
            $this->showModal(ModalActions::PREV);
            return;
        }
        $this->internalClear(); // close current
        // fetch previous

        // not last item
        $currentIndex = $this->data['currentIndex'];
        $transactionsArr = $this->data['transactions'];

        if($currentIndex > 0)
        {
            $currentIndex--;
            \Session::put('review.index', $currentIndex); // update session
            $this->data['currentIndex'] = $currentIndex;
            $this->fetch($transactionsArr[$currentIndex]); // ID
            $this->emit('ReviewItemChanged', $this->data['trx']['audio']);
        }
    }

    public function copyForCorrection(): void
    {
        if($this->data['trx']['status'] != CorrectionTypes::CORRECTED->value)
        {
            $this->data['correctedText'] = $this->data['originalText'];
        }
        $this->data['editing'] = true;

    }

    public function close(): void
    {
        $this->internalClear();

        \Redirect::route("dashboard");
    }


    public function internalClear(): void
    {
//        if($this->data['trx']['status'] != CorrectionTypes::CORRECTED->value)
//        {
            $this->data['correctedText'] = "";
            $this->data['subDiff'] = "";
            $this->data['orgDiff'] = "";
//        }
//        else
//        {
            // todo
//        }
        $this->data['editing'] = false;
    }

    public function clearAndReset(): void
    {
        $this->internalClear();
        $this->refresh();
    }

    public function calculateDiffPercentage($oldText, $newText): int
    {
        return (mb_levenshtein_ratio($oldText, $newText)*100);
    }

    public function downloadAudio()
    {
        $audioData = $this->data['trx']['audio']['audioData']; // Replace with the actual audio data

        $filename = $this->data['trx']['reviewId']; // Provide a suitable filename for the audio file
        $contentType = 'audio/wav'; // default

        switch ($this->data['trx']['audio']['audioType']) {
            case "audio/ogg":
                $filename .= ".ogg";
                $contentType = "audio/ogg";
                break;
            default:
                $filename .= '.wav';
                $contentType = 'audio/wav';
                break;
        }

        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

//        dd($headers, $filename, $audioData);
        return response()->streamDownload(function () use ($audioData) {
            echo $audioData;
        }, $filename, $headers);
    }

    public function downloadText()
    {
        $data = $this->data['originalText']; // Replace with the actual audio data

        $filename = $this->data['trx']['reviewId'].'.txt'; // Provide a suitable filename for the audio file

        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($data) {
            echo $data;
        }, $filename, $headers);
    }


    public function reject()
    {

//        return;
        $response = NvoqNetworkController::SubmitCorrection(
            $this->data['trx']['id'],
            new CorrectionParameters(CorrectionTypes::REJECTED, "", null)
        ); // Make API call to reject transaction

//        dd(session()->all());
//        if($response == null || !is_array($response)) return; // abort

        if(!$response) return;

        // if OK, Clear, Refresh, Flash success result
        $this->internalClear();
        $this->refresh();
        session()->flash("success", [__("alert-success-title")  => __("alert-correction-success-content", ['status' => CorrectionTypes::REJECTED->value])]);
    }


    /**
     * Call nvoq to submit correction with status 'correction'
     * @return void
     */
    public function correct()
    {

//        return;
        $response = NvoqNetworkController::SubmitCorrection(
            $this->data['trx']['id'],
            new CorrectionParameters(CorrectionTypes::CORRECTED, $this->data['correctedText'], null)
        ); // Make API call to reject transaction

//        dd(session()->all());
//        if($response == null || !is_array($response)) return; // abort

        if(!$response) return;

        /* do something? */
        // if OK, Clear, Refresh, Flash success result
        $this->internalClear(); // clear stuff
        $this->refresh();
        session()->flash("success", [
            __("alert-success-title") // bold
            =>  // msg
                __("alert-correction-success-content", ['status' => CorrectionTypes::CORRECTED->value])
        ]);

    }

    public function submitCorrection()
    {
        $tmpAccuracy = $this->calculateDiffPercentage($this->data['originalText'], $this->data['correctedText']);
        $tmpDifference = 100 - $tmpAccuracy;
        if($tmpDifference > 20)
        {
            $this->showModal(ModalActions::CORRECT_OR_REJECT, ['diff' => $tmpDifference]);
        }else{
            /* Submit */
            $this->correct();
        }
    }

    //region Modal Functions
    private function showModal(ModalActions $action, $args = []): void
    {
        switch ($action)
        {
            case ModalActions::NONE:
                /* Nothing */
                $title = __("alert-error-title");
                $content = __("alert-error-content");
                break;

            case ModalActions::CORRECT_OR_REJECT:
                $title = __("alert-correct-or-reject-title");
                $content = __("alert-correct-or-reject-content", $args);
                break;
            case ModalActions::NEXT:
            case ModalActions::PREV:
                $title = __("alert-pending-changes-title");
                $content = __("alert-pending-changes-content");
                break;
            /*case ModalActions::CORRECTION_SUCCESS:
                $title = __("alert-success-title");
                $content = __("alert-correction-success-content", $args);
                break;*/
            case ModalActions::CLOSE:
                break;
        }

        if(!empty($title) && !empty($content)) {
            $this->data["modalData"]["title"] = $title;
            $this->data["modalData"]["content"] = $content;
        }
        $this->modalAction = $action;
        $this->data["modalOpen"] = true;
    }

    private function closeModal(): void
    {
        $this->data["modalOpen"] = false;
        $this->modalAction = ModalActions::NONE;
    }

    public function modalConfirm()
    {
        // do something
        switch ($this->modalAction)
        {
            case ModalActions::NEXT:
                $this->data['editing'] = false;
                $this->next();
                break;
            case ModalActions::PREV:
                $this->data['editing'] = false;
                $this->prev();
                break;
            /*case ModalActions::CORRECTION_SUCCESS:
                $this->closeModal(); // close current

                $this->refresh(); // refresh (any correction (reject/correction/etc..))
                return; // stop execution*/

            case ModalActions::CORRECT_OR_REJECT:
                /** Submit As Rejection */
                $this->closeModal(); // close current

                $this->reject(); // reject
            return; // stop execution

            case ModalActions::NONE:
            case ModalActions::CLOSE:
                /* Nothing */
                break;
        }
//        dd(3,$this->data, $this->modalAction);
        $this->closeModal();
    }

    public function modalCancel(): void
    {
        if($this->modalAction == ModalActions::CORRECT_OR_REJECT)
        {
            /** Submit as correction */
            $this->closeModal(); // close current
            $this->correct(); // submit correction request to nvoq
            return; // stop execution

        }
        // do something
        $this->closeModal();
    }
    //endregion


    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
//        dd($this->data['trx'], $this->data);
//        if(session()->has('trx')) {

        if(\Arr::has(($this->data['trx']), 'id')) {
//        if(true) {
            // Diff data
            if(!empty($this->data['correctedText'] && $this->data['trx']['status'] == CorrectionTypes::CORRECTED->value))
            {
                // re-diff
                // use the JSON result to render in HTML
                $jsonResult = DiffHelper::calculate($this->data['originalText'], $this->data['correctedText'], 'Json'); // may store the JSON result in your database
                $htmlRenderer = RendererFactory::make('Inline', $this->getRenderOptions());
                $this->data['orgDiff'] = $htmlRenderer->renderArray(json_decode($jsonResult, true));
//                dd($htmlRenderer->getOptions());
                $this->data["accuracy"] = $this->calculateDiffPercentage($this->data['originalText'], $this->data['correctedText']);

                $jsonResult2 = DiffHelper::calculate($this->data['substitutedText'], $this->data['correctedText'], 'Json'); // may store the JSON result in your database
                $this->data['subDiff'] = $htmlRenderer->renderArray(json_decode($jsonResult2, true));
//                dd($this->data['originalText'], $this->data['correctedText'],$jsonResult, $htmlRenderer, $jsonResult2, $this->data['subDiff']);
            }
//            dd($this->data, $this->data['trx']);
//            dd($this->data['originalText'], $this->data['correctedText'],$this->data, !empty($this->data['correctedText'] && $this->data['trx']['status'] == CorrectionTypes::CORRECTED->value));
            return view('livewire.review-component', [
                'trx' => $this->data['trx'],
                'data' => $this->data
            ]);
        }
        // else: parent view will return back
        return "<div>No data</div>";
    }

    /**
     * Clears flashed session error message
     * @return void
     */
    public function clearError()
    {
        session()->forget('error');
    }

    /**
     * Clears flashed session success message
     * @return void
     */
    public function clearSuccess()
    {
        session()->forget('success');
    }

    /**
     * Refreshes current job data from nvoq
     * @return void
     */
    public function refresh()
    {
        $this->fetch($this->data['transactions'][$this->data['currentIndex']]);
    }

    private function getRenderOptions()
    {
        return [
            // how detailed the rendered HTML in-line diff is? (none, line, word, char)
            'detailLevel' => 'char',
            // renderer language: eng, cht, chs, jpn, ...
            // or an array which has the same keys with a language file
            // check the "Custom Language" section in the readme for more advanced usage
            'language' => 'eng',
            // show line numbers in HTML renderers
            'lineNumbers' => true,
            // show a separator between different diff hunks in HTML renderers
            'separateBlock' => true,
            // show the (table) header
            'showHeader' => true,
            // the frontend HTML could use CSS "white-space: pre;" to visualize consecutive whitespaces
            // but if you want to visualize them in the backend with "&nbsp;", you can set this to true
            'spacesToNbsp' => false,
            // HTML renderer tab width (negative = do not convert into spaces)
            'tabSize' => 4,
            // this option is currently only for the Combined renderer.
            // it determines whether a replace-type block should be merged or not
            // depending on the content changed ratio, which values between 0 and 1.
            'mergeThreshold' => 0.8,
            // this option is currently only for the Unified and the Context renderers.
            // RendererConstant::CLI_COLOR_AUTO = colorize the output if possible (default)
            // RendererConstant::CLI_COLOR_ENABLE = force to colorize the output
            // RendererConstant::CLI_COLOR_DISABLE = force not to colorize the output
            'cliColorization' => RendererConstant::CLI_COLOR_AUTO,
            // this option is currently only for the Json renderer.
            // internally, ops (tags) are all int type but this is not good for human reading.
            // set this to "true" to convert them into string form before outputting.
            'outputTagAsString' => false,
            // this option is currently only for the Json renderer.
            // it controls how the output JSON is formatted.
            // see available options on https://www.php.net/manual/en/function.json-encode.php
            'jsonEncodeFlags' => \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
            // this option is currently effective when the "detailLevel" is "word"
            // characters listed in this array can be used to make diff segments into a whole
            // for example, making "<del>good</del>-<del>looking</del>" into "<del>good-looking</del>"
            // this should bring better readability but set this to empty array if you do not want it
            'wordGlues' => [' ', '-'],
            // change this value to a string as the returned diff if the two input strings are identical
            'resultForIdenticals' => '100% Match',
            // extra HTML classes added to the DOM of the diff container
            'wrapperClasses' => ['diff-wrapper'],
        ];

    }

    //region Listeners/Events
    public function addToVocab($word, $soundsLike)
    {
        $res['error'] = false;
        $res['msg'] = "";

        $word = $this->sanitizeString($word);
        $soundsLike = $this->sanitizeString($soundsLike);

        if(!\Auth::user()->isAdmin())
        {
            $res['error'] = true;
            $res['msg'] = "Permission Denied";
        }
        else if(\Str::length($word) == 0)
        {
            $res['error'] = true;
            $res['msg'] = "Word cannot be empty";
        }
        else
        {
            // nvoq save vocab
            $res = NvoqNetworkController::AddVocab($word, $soundsLike);

        }

        $this->emit('addToVocabCallback', $res);
    }
    //endregion

    private function sanitizeString($str)
    {
        $str = \Str::ascii($str);
        return \Str::of($str)->trim()->toString();
    }
}
