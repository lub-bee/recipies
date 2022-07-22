<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Receipe;
use App\Models\Requirement;
use App\Models\Step;
use App\Models\Unite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view("receipe.index")->with("receipes",Receipe::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("receipe.create");
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
        $validated = $request->validate([
            'name' => 'required|max:64',
            'preparation_time' => 'required|numeric|nullable',
            'cooking_time' => 'required|numeric|nullable',
        ]);

        $id = Receipe::insertGetId([
            "name" => $validated["name"],
            "preparation_time" => $validated["preparation_time"],
            "cooking_time" => $validated["cooking_time"],
            "author" => Auth::user()->id,
        ]);

        return redirect()->route("requirement",$id);
    }

    public function store_step(Request $request){

        $validated = $request->validate([
            'order' => 'required|numeric',
            'description' => 'required',
            'receipe_id' => 'required|exists:receipes,id'
        ]);

        $receipe = Receipe::findOrFail($validated["receipe_id"]);
        if($receipe->author !== Auth::user()->id){
            abort(403);
        }

        $id = Step::insertGetId([
            "order" => $validated["order"],
            "description" => $validated["description"],
            "receipe_id" => $receipe->id,
        ]);

        return redirect()->route("receipe.edit",$receipe->id);
    }

    public function store_requirement(Request $request, $receipe_id){
        $validated = $request->validate([
            'ingredient' => 'required|max:64',
            'unite_id' => 'required|numeric|exists:unites,id',
            'quantity' => 'required|numeric',
        ]);

        // dd($validated);

        $receipe = Receipe::findOrFail($receipe_id);
        if($receipe->author !== Auth::user()->id){
            abort(403);
        }

        $ingredient = Ingredient::where(["name" => $validated["ingredient"]])->firstOrFail();

        Requirement::insert([
            "receipe_id" => $receipe->id,
            "ingredient_id" => $ingredient->id,
            "unite_id" => $validated["unite_id"],
            "quantity" => $validated["quantity"],
        ]);

        return redirect()->route("receipe.edit",$receipe->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view("receipe.show")->with("receipe",Receipe::findOrFail($id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($receipe_id)
    {
        return view("receipe.edit")
            ->with("receipe", Receipe::findOrFail($receipe_id))
            ->with("unites", Unite::all());
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
        dd("here?");
        Receipe::where(["id"=> $id, "author" => Auth::user()->id])->delete();

        return redirect()->route("receipe.index")->with("msg","La recette a bien été supprimée.");
    }

    public function destroy_requirement(Request $request){

        $validated = $request->validate([
            'receipe_id' => 'required|exists:receipes,id',
            'id' => 'required|exists:requirements,id',
        ]);
        // Requirement::where(["id"=> ])
    }
}
