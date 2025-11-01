<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\CollaboratorController;
use App\Http\Requests\ImportFileRequest;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Http\Resources\CollaboratorResource;
use App\Models\Collaborator;
use App\Models\User;
use App\Services\CollaboratorService;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CollaboratorControllerTest extends TestCase
{
    /** @var MockInterface */
    private $serviceMock;
    private CollaboratorController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceMock = Mockery::mock(CollaboratorService::class);
        /** @var CollaboratorService $serviceForCtor */
        $serviceForCtor = $this->serviceMock;
        $this->controller = new CollaboratorController($serviceForCtor);
    }

    private function signIn(User $user): void
    {
        /** @var AuthenticatableContract $authUser */
        $authUser = $user;
        $this->be($authUser);
    }

    public function test_index_calls_service_list_and_returns_resource_collection(): void
    {
        $user = User::factory()->create();
        $this->signIn($user);

        $collabs = collect([
            Collaborator::make(['id' => 1, 'name' => 'A', 'email' => 'a@example.com', 'cpf' => '11111111111', 'city' => 'X', 'state' => 'SP', 'user_id' => $user->id]),
            Collaborator::make(['id' => 2, 'name' => 'B', 'email' => 'b@example.com', 'cpf' => '22222222222', 'city' => 'Y', 'state' => 'RJ', 'user_id' => $user->id]),
        ]);
        $paginator = new LengthAwarePaginator($collabs, $collabs->count(), $collabs->count());

        $this->serviceMock
            ->shouldReceive('list')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn($paginator);

        $request = Request::create('/collaborators', 'GET', ['search' => 'a']);
        $response = $this->controller->index($request);

        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $response);
        $this->assertSame(CollaboratorResource::class, $response->collects);
    }

    public function test_store_calls_service_create_and_returns_resource(): void
    {
        $user = User::factory()->create();
        $this->signIn($user);

        $payload = [
            'name' => 'New', 'email' => 'new@example.com', 'cpf' => '33333333333', 'city' => 'Z', 'state' => 'SP', 'user_id' => $user->id,
        ];
        $created = new Collaborator($payload + ['id' => 10]);

        $this->serviceMock
            ->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn($created);

        $request = Mockery::mock(StoreCollaboratorRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($payload);

        $resource = $this->controller->store($request);

        $this->assertInstanceOf(CollaboratorResource::class, $resource);
        $this->assertSame('New', $resource->resource->name);
    }

    public function test_show_returns_resource_for_bound_model(): void
    {
        $collab = Collaborator::make(['id' => 123, 'name' => 'Shown', 'email' => 's@example.com', 'cpf' => '44444444444', 'city' => 'C', 'state' => 'SP', 'user_id' => 1]);

        $resource = $this->controller->show($collab);

        $this->assertInstanceOf(CollaboratorResource::class, $resource);
        $this->assertSame('Shown', $resource->resource->name);
    }

    public function test_update_calls_service_update_and_returns_resource(): void
    {
        $collab = Collaborator::make(['id' => 20, 'name' => 'Old', 'email' => 'o@example.com', 'cpf' => '55555555555', 'city' => 'C', 'state' => 'SP', 'user_id' => 1]);
        $data = ['name' => 'Updated'];
        $updated = Collaborator::make(['id' => 20, 'name' => 'Updated', 'email' => 'o@example.com', 'cpf' => '55555555555', 'city' => 'C', 'state' => 'SP', 'user_id' => 1]);

        $this->serviceMock
            ->shouldReceive('update')
            ->once()
            ->with($collab, $data)
            ->andReturn($updated);

        $request = Mockery::mock(UpdateCollaboratorRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($data);

        $resource = $this->controller->update($request, $collab);

        $this->assertInstanceOf(CollaboratorResource::class, $resource);
        $this->assertSame('Updated', $resource->resource->name);
    }

    public function test_destroy_calls_service_delete_and_returns_message(): void
    {
        $collab = Collaborator::make(['id' => 30]);

        $this->serviceMock
            ->shouldReceive('delete')
            ->once()
            ->with($collab)
            ->andReturnNull();

        $response = $this->controller->destroy($collab);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('Collaborator has been deleted successfully', $response->getData(true)['message']);
    }

    public function test_importByFile_calls_service_import_and_returns_message(): void
    {
        $user = User::factory()->create();
        $this->signIn($user);

        $request = Mockery::mock(ImportFileRequest::class);

        $this->serviceMock
            ->shouldReceive('import')
            ->once()
            ->with($request)
            ->andReturn([
                'success' => true,
                'message' => 'Arquivo recebido e importação iniciada em background.',
            ]);

        $result = $this->controller->importByFile($request);

        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
    }
}
