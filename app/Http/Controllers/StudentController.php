<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Para DataTables, obtenemos todos los estudiantes sin paginación
        // DataTables manejará la paginación, búsqueda y ordenamiento del lado del cliente
        $students = Student::orderBy('last_name')->orderBy('first_name')->get();

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'student_number' => 'required|string|unique:students',
            'email' => 'required|email|unique:students',
            'phone' => 'nullable|string',
            'year' => 'required|integer|min:1|max:6',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Estudiante creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'student_number' => 'required|string|unique:students,student_number,' . $student->id,
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string',
            'year' => 'required|integer|min:1|max:6',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Estudiante actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        // Desactivar en lugar de eliminar, y también cambiar status
        $student->update([
            'is_active' => false,
            'status' => 'inactive'
        ]);
        return redirect()->route('students.index')->with('success', 'Estudiante desactivado exitosamente.');
    }

    /**
     * Search students for POS
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');

        $students = Student::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('student_number', 'LIKE', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            })
            ->limit(10)
            ->get();

        return response()->json($students);
    }
}
