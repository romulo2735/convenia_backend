<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportFileRequest;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Http\Resources\CollaboratorResource;
use App\Models\Collaborator;
use App\Services\CollaboratorService;
use Illuminate\Http\Request;

class CollaboratorController extends Controller
{
    public function __construct(protected CollaboratorService $service)
    {
    }

    public function index(Request $request)
    {
        $collaborators = $this->service->list($request->all());

        return CollaboratorResource::collection($collaborators);
    }


    public function store(StoreCollaboratorRequest $request)
    {
        $collaborator = $this->service->create($request->validated());

        return new CollaboratorResource($collaborator);
    }


    public function show(Collaborator $collaborator)
    {
        return new CollaboratorResource($collaborator);
    }


    public function update(UpdateCollaboratorRequest $request, Collaborator $collaborator)
    {
        $collaborator = $this->service->update($collaborator, $request->validated());

        return new CollaboratorResource($collaborator);
    }


    public function destroy(Collaborator $collaborator)
    {
        $this->service->delete($collaborator);
        return response()->json(['message' => 'Collaborator has been deleted successfully']);
    }

    public function importByFile(ImportFileRequest $request)
    {
        $this->service->import($request);

        return [
            'success' => true,
            'message' => 'Arquivo recebido. O processamento será feito em segundo plano.',
        ];
    }
}
