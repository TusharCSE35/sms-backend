<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->students()->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $student = $request->user()->students()->create($data);

        return response()->json($student, 201);
    }

    public function show(Request $request, string $id)
    {
        return $this->findOwned($request, $id);
    }

    public function update(Request $request, string $id)
    {
        $student = $this->findOwned($request, $id);
        $student->update($this->validated($request, $student->id));

        return $student;
    }

    public function destroy(Request $request, string $id)
    {
        $this->findOwned($request, $id)->delete();

        return response()->noContent();
    }

    private function findOwned(Request $request, string $id): Student
    {
        return $request->user()->students()->findOrFail($id);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'       => 'required|string|min:2|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:20',
            'roll'       => [
                'required', 'string', 'max:50',
                Rule::unique('students', 'roll')
                    ->where('user_id', $request->user()->id)
                    ->ignore($ignoreId),
            ],
            'department' => 'required|string|max:100',
        ]);
    }
}