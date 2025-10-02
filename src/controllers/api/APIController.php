<?php
require_once __DIR__ . '/../../services/CanvasReader.php';
require_once __DIR__ . '/../../services/ConfigProvider.php';
function erasethis($buffer)
{
    return "";
}
abstract class APIController {
    protected CanvasReader $canvasReader;
    public function __construct(CanvasReader $canvasReader){
        $this->canvasReader = $canvasReader;
    }
    protected $debug_keep_output = false;
    public function index(){
        try{
            if(!$this->debug_keep_output){
                ob_start("erasethis");
            }
            $data = $this->handle();
            if(!$this->debug_keep_output){
                ob_end_flush();
            }
            header('Content-Type: application/json');
            echo json_encode($data);
            // var_dump($_SESSION['cache']["values"]);
        }catch(Exception $e){
            if(!$this->debug_keep_output){
                ob_end_flush();
            }
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["error" => $e->getMessage(), "trace" => explode("\n", $e->getTraceAsString())]);
        }
    }

    public abstract function handle();
}