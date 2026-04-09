<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ExportCustomerRequest;
use App\Http\Requests\Deal\ExportDealRequest;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly CsvExportService $exportService,
    ) {}

    /**
     * GET /api/customers/export
     */
    public function customers(ExportCustomerRequest $request): StreamedResponse
    {
        return $this->exportService->exportCustomers($request->validated());
    }

    /**
     * GET /api/deals/export
     */
    public function deals(ExportDealRequest $request): StreamedResponse
    {
        return $this->exportService->exportDeals($request->validated());
    }
}
