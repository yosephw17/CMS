<?php
// app/Http/Controllers/EvaluatorController.php
namespace App\Http\Controllers;

use App\Models\Evaluator;
use App\Http\Requests\StoreEvaluatorRequest;
use App\Http\Requests\UpdateEvaluatorRequest;

class EvaluatorController extends Controller
{
    public function index()
    {
        return view('evaluators.index', [
            'evaluators' => Evaluator::latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return view('evaluators.create');
    }

    public function store(StoreEvaluatorRequest $request)
    {
        Evaluator::create($request->validated());
        return redirect()->route('evaluators.index')->with('success', 'Evaluator created!');
    }

    public function show(Evaluator $evaluator)
    {
        return view('evaluators.show', compact('evaluator'));
    }

    public function edit(Evaluator $evaluator)
    {
        return view('evaluators.edit', compact('evaluator'));
    }

    public function update(UpdateEvaluatorRequest $request, Evaluator $evaluator)
    {
        $evaluator->update($request->validated());
        return redirect()->route('evaluators.index')->with('success', 'Evaluator updated!');
    }

    public function destroy(Evaluator $evaluator)
    {
        $evaluator->delete();
        return redirect()->route('evaluators.index')->with('success', 'Evaluator deleted!');
    }
}