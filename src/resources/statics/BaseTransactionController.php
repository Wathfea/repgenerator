<?php

namespace App\Abstraction\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class BaseTransactionController.
 */
class BaseTransactionController extends Controller
{
    /**
     * @param  string  $method
     * @param  array  $parameters
     * @return Response|View|AnonymousResourceCollection
     * @throws Throwable
     */
    public function callAction($method, $parameters): Response|View|AnonymousResourceCollection
    {
        if (config('testing.env') == 'pipeline') {
            return parent::callAction($method, $parameters);
        }
        try {
            DB::beginTransaction();
            $result = parent::callAction($method, $parameters);
            if (DB::connection()->getPdo()->inTransaction()) {
                DB::commit();
            }
        } catch (Exception $exception) {
            if (DB::connection()->getPdo()->inTransaction()) {
                DB::rollBack();
            }
            Log::error($exception->getMessage());
            throw $exception;
        }

        return $result;
    }
}
