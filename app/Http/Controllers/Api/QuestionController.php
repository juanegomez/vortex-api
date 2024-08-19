<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Questions;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Services\QuestionService;
use App\Http\Resources\QuestionsResource;
use App\Http\Resources\QuestionResource;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function index(Request $request)
    {
        // Número de ítems por página
        $perPage = $request->query('per_page', 15);

        $questions = Questions::orderBy('id', 'DESC')->paginate($perPage);

        $data = [
            'questions' => QuestionsResource::collection($questions),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'last_page' => $questions->lastPage(),
            ],
            'status' => Response::HTTP_OK,
        ];

        return response()->json($data, Response::HTTP_OK);
    }

    //Obtener una pregunta junto con sus respuestas.
    public function show($id)
    {
        // Validar el ID de la pregunta
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:questions,id'
        ]);

        // Si la validación falla, retorna un error
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid ID',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Obtener la pregunta por ID
        $question = Questions::with('answers')->find($id);

        // Verificar si la pregunta existe
        if (!$question) {
            return response()->json([
                'message' => 'Pregunta no encontrada.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Retornar la pregunta con el formato deseado
        $data = [
            'question' => new QuestionResource($question),
            'status' => Response::HTTP_OK,
        ];

        return response()->json($data, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // Llamar al servicio para almacenar la pregunta con respuestas
        $result = $this->questionService->store($request->all());

        // Manejar la respuesta del servicio
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null,
                'status' => $result['status'],
            ], $result['status']);
        }

        return response()->json([
            'message' => $result['message'],
            'status' => $result['status'],
        ], $result['status']);
    }

    /**
     * Valida si la respuesta proporcionada es correcta para una pregunta dada.
    */
    public function validateAnswer(Request $request)
    {
        $result = $this->questionService->validateAnswer($request->all());
    
        return response()->json([
            'message' => $result['message'],
            'status' => $result['status'],
            'is_correct' => $result['is_correct'],
        ], $result['status']);
    }
}
