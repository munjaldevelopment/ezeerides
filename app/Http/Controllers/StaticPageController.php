<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Models\Setting;
use App\Models\Page;

class StaticPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        echo "hi";
    }

    public function termsPage()
    {
        //
       $pageInfo = Page::getPageInfo(1);
       
       //$pageData = json_decode($pageInfo->name);
        //dd($pageData);
        return view('page', ['title' => $pageInfo->title, 'meta_description' => $pageInfo->title, 'meta_keywords' => $pageInfo->title, 'pageheading' => $pageInfo->title, 'content' => $pageInfo->content]);
    }

    public function privacyPage()
    {
        //
       $pageInfo = Page::getPageInfo(2);
       
       //$pageData = json_decode($pageInfo->name);
        //dd($pageData);
        return view('page', ['title' => $pageInfo->title, 'meta_description' => $pageInfo->title, 'meta_keywords' => $pageInfo->title, 'pageheading' => $pageInfo->title, 'content' => $pageInfo->content]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
