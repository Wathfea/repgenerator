<?php

namespace Pentacom\Repgenerator;

use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFilterService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorStaticFilesService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorStubService;
use Tests\TestCase;

class RepgeneratorServiceTests extends TestCase {

    public function test_can_instantiate_main_service() {
        $this->assertInstanceOf(RepgeneratorService::class, app(RepgeneratorService::class));
    }

    public function test_can_instantiate_static_files_service() {
        $this->assertInstanceOf(RepgeneratorStaticFilesService::class, app(RepgeneratorStaticFilesService::class));
    }

    public function test_can_instantiate_stub_file_service() {
        $this->assertInstanceOf(RepgeneratorStubService::class, app(RepgeneratorStubService::class));
    }

    public function test_can_instantiate_filter_service() {
        $this->assertInstanceOf(RepgeneratorFilterService::class, app(RepgeneratorFilterService::class));
    }

    public function test_can_get_stub_file() {
        /** @var RepgeneratorStubService $stubService */
        $stubService = app(RepgeneratorStubService::class);
        $this->assertNotEmpty($stubService->getStub('Model'));
    }

    public function test_can_get_static_file() {
        /** @var RepgeneratorStaticFilesService $staticFileService */
        $staticFileService = app(RepgeneratorStaticFilesService::class);
        $this->assertNotEmpty($staticFileService->getStatic('BaseModel'));
    }
}
