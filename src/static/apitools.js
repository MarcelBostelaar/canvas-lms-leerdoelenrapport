const MAX_CONCURRENT = 5;
const BURST_SIZE = 20; //number of requests to start at once
const BURST_AT_SPEED = 250;
let running = 0, queue = [];

// Track total elapsed time and count for running average
let runningAverage = 10000;

function registerCallbackInQueue(callback) {
    queue.push(callback);
}

function launchOneOffQueue(){
    const callback = queue.shift();
    if(!callback) return;
    running++;
    const start = Date.now();
    callback().finally(() => {
        let elapsed = Date.now() - start;
        runningAverage = (runningAverage + elapsed) / 2; // Simple moving average
        // console.log("Elapsed time: " + elapsed.toString() + "\nAverage time: " + runningAverage.toString() + " ms");
        running--;
        processQueue();
    });
}

function processQueue() {
    if(runningAverage < BURST_AT_SPEED){
        //If we are fast, launch a burst of requests
        let burstCount = Math.min(BURST_SIZE, queue.length);
        // console.log("Bursting " + burstCount + " requests");
        runningAverage = 10000; //reset average to avoid repeated bursts
        for(let i=0; i<burstCount; i++){
            launchOneOffQueue();
        }   
        return;
    }
    while (running < MAX_CONCURRENT && queue.length > 0) {
        launchOneOffQueue();
    }
}

// Returns the running average elapsed time per request in ms
function getAverageElapsedTime() {
    return elapsedCount === 0 ? 0 : runningAverage / elapsedCount;
}