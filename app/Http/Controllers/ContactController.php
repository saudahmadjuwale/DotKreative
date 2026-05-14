<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submit(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'number' => 'required'
        ]);

        dd($request->all());

    }
}