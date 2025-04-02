<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function view(ReviewRequest $request)
    {
        /*$request->validate([
            'transactions' => ['required', 'array'],
            'index' => ['required', 'integer', 'min:0', 'max:'.(sizeof($request['transactions']))]
        ]);*/
//        session()->put('review', $request->only('transactions', 'index'));
//        return view('review', []);
//        dd(session()->all(), session()->has('review'), session("review.transactions"));
        if(session()->has('review'))
        {
            try {
                \Validator::validate(session('review'), [
                    'transactions' => ['required', 'array'],
                    'index' => ['required', 'integer', 'min:0', 'max:'.(sizeof(session("review.transactions")))]
                ]);
                return view('review');
            }catch (ValidationException)
            {
                return redirect()->route('dashboard')->withErrors(['global' => 'invalid review data']);
            }
//            return back()->withErrors(['global' => 'invalid review data']);
        }else{
//            return view('review', $request->only('transactions', 'index'));
//            return view('review', $request->only('transactions', 'index'));
//            return view('review', ['trx' => session('trx')]);
            return redirect()->route('dashboard')->withErrors(['global' => 'invalid review data']);

        }
    }
}
