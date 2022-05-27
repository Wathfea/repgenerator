<?php

namespace App\Abstraction\CRUD\Controllers;

use App\Abstraction\Filter\BaseQueryFilter;
use App\Abstraction\Repository\Service\RepositoryServiceInterface;
use App\Abstraction\CRUD\Enums\CRUDConfigType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Database\Eloquent\Model;
use App\Abstraction\Controllers\BaseTransactionController;

abstract class AbstractCRUDController extends BaseTransactionController implements CRUDControllerInterface
{
    /**
     * @var array
     */
    protected array $crudConfig = [];

    /**
     * @param array $config
     * @return CRUDControllerInterface
     */
    public function setCRUDConfig(array $config): CRUDControllerInterface {
        $this->crudConfig = $config;
        return $this;
    }


    public function __construct(protected RepositoryServiceInterface $service) {
    }

    /**
     * @param string $config
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $config, mixed $default = null): mixed {
        if ( key_exists($config, $this->crudConfig) ) {
            return $this->crudConfig[$config];
        }
        return $default;
    }

    /**
     * @return array
     */
    public function getLoad(): array {
        return $this->getConfig(CRUDConfigType::LOAD, []);
    }

    /**
     * @return string|null
     */
    public function getResource(): string|null {
        return $this->getConfig(CRUDConfigType::RESOURCE);
    }

    /**
     * @param array $data
     * @return array
     */
    public function getData(array $data = []) : array {
        $data = array_merge($data, [
            'model' => $this->service->getModelName()
        ]);
        $extraData = $this->getConfig(CRUDConfigType::EXTRA_DATA, []);
        foreach ( $extraData as $key => $extraDatum ) {
            if ( is_callable($extraDatum) ) {
                $extraData[$key] = $extraDatum();
            }
        }
        return array_merge($data, $extraData);
    }

    /**
     * @param Request $request
     * @param int $default
     * @return mixed
     */
    protected function getPerPage(Request $request, int $default = 10): mixed {
        $perPage = $request->get('per_page');
        if ( $perPage > 0 ) {
            return $perPage;
        }
        $configPerPage = $this->getConfig(CRUDConfigType::PAGINATE);
        return !empty($configPerPage) ? $configPerPage : $default;
    }


    /**
     * @param int $id
     * @return Model|JsonResource|null
     */
    protected function getEditData(int $id): Model|JsonResource|null
    {
        $data = $this->service->getById($id, $this->getLoad());
        /** @var JsonResource $editResource */
        $editResource = $this->getResource() ;
        if ( $editResource ) {
            $data = $editResource::make($data);
        }
        return $data;
    }


    /**
     * @param Request $request
     * @return LengthAwarePaginator|AnonymousResourceCollection|Collection
     */
    protected function getIndexData(Request $request): LengthAwarePaginator|AnonymousResourceCollection|Collection
    {
        $perPage = $this->getPerPage($request);

        $data = $this->service->getByFilter(array_merge($request->all(), [BaseQueryFilter::PER_PAGE => $perPage]), $this->getLoad());

        /** @var JsonResource $indexResource */
        $indexResource = $this->getResource();
        if ( $indexResource ) {
            $data = $indexResource::collection($data);
        }
        return $data;
    }
}
