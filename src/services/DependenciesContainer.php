<?php
require_once __DIR__ . '/CanvasReader.php';
require_once __DIR__ . '/CanvasLeerdoelProvider.php';
require_once __DIR__ . '/ConfigProvider.php';
require_once __DIR__ . '/GroupingProvider.php';
require_once __DIR__ . '/LeerdoelenStructuurProvider.php';
require_once __DIR__ . '/StudentProvider.php';
require_once __DIR__ . '/../debug/DummyDataCanvasReader.php';
require_once __DIR__ . '/../debug/DummyStudentResultProvider.php';

class DependenciesContainer
{
    public CanvasReader $canvasReader;
    public CanvasLeerdoelProvider $canvasLeerdoelProvider;
    public ConfigProvider $configProvider;
    public GroupingProvider $groupingProvider;
    public LeerdoelenStructuurProvider $leerdoelenStructuurProvider;
    public StudentProvider $studentProvider;
}

function readerFromEnv(): CanvasReader{
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $apiKey = $env['APIKEY'];
    $baseURL = $env['baseURL'];
    $courseID = $env['courseID'];
    return new CanvasReader($apiKey, $baseURL, $courseID);
}

function setupGlobalDependencies(): void
{
    $dependencies = new DependenciesContainer();

    $dependencies->canvasReader = readerFromEnv();
    $dependencies->configProvider = new ConfigProvider();
    $dependencies->canvasLeerdoelProvider = new CanvasLeerdoelProvider();
    $dependencies->leerdoelenStructuurProvider = new LeerdoelenStructuurProvider();
    $dependencies->groupingProvider = new GroupingProvider();
    $dependencies->studentProvider = new StudentProvider();
    
    //Debug
    $dependencies->studentProvider = new DummyStudentResultProvider();

    //set global provider variable
    $GLOBALS["providers"] = $dependencies;
}