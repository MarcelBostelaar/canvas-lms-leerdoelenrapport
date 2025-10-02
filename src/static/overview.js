function rescaleValueToRange(value, originalMin, originalMax, newMin, newMax) {
    return ((value - originalMin) / (originalMax - originalMin)) * (newMax - newMin) + newMin;
}

function calculateRGBFromScore(score) {
    const exponent = 25;
    if( score == 1 ) {
        return 'RGB(0,255,0)'; // Green
    }
    let newScore = score**exponent; // Make the scale non-linear. 
    //Max out at 200, so real green is visibly different from almost green
    let green = Math.floor(Math.sqrt(rescaleValueToRange(newScore, 0, 1, 0, 200**2)));
    //invert score, so 1 is red and 0 is green
    let red = Math.floor(Math.sqrt(rescaleValueToRange(1 - newScore, 0, 1, 55**2, 255**2)));
    console.log(`Score: ${score}, Red: ${red}, Green: ${green}`);
    return `RGB(${red},${green},0)`;
}

let studentData = {};

function populateProgressBox(progress_box, data){
    let text = '';
    text += "On track: " + (data.on_track + data.exceeded).toString() + " / " + data.total.toString();
    text += "<br>Aantal niveaus achterstand: " + data.points_behind.toString();
    text += " - Aantal leerdoelen voorsprong: " + data.exceeded.toString();

    progress_box.innerHTML = text;

    progress_box.style.backgroundColor = calculateRGBFromScore(1 - (data.points_behind / data.total_points_needed));
}

function fetchStudentProgressSummary(progress_box, studentID, currentPeriod){
    return fetch(`/controllers/api/StudentProgressSummary.php?id=${studentID}&currentPeriod=${currentPeriod}`)
    .then(response => response.json())
    .then(data => {
        studentData[studentID] = data;
        populateProgressBox(progress_box, data);
    })
    .catch(error => {
        console.error('Error fetching student progress summary:', error);
        progress_box.innerHTML = 'Error';
    });
}

function PrefetchStudentResults(studentID) {
    return fetch(`/controllers/api/PrefetchStudentResults.php?id=${studentID}`)
    .then(_ => {
        console.log(`Prefetched results for student ${studentID}`);
        document.getElementById(`progress_box_${studentID}`).classList.add('prefetched')
    });
}

function refresh(){
    $promises = [];
    for(let [id, data] of Object.entries(studentData)){
        $promises.push(
            fetch(`/controllers/ClearCacheController.php?studentID=${id}`)
        );
    }
    Promise.all($promises).then(_ => {
        console.log("Cache cleared, refreshing page");
        window.location.reload();
    });
}

document.addEventListener("DOMContentLoaded", function() {
    let elementsToLoad = Array.from(document.querySelectorAll('.progress_box'));
    let processed = elementsToLoad.map(element => {
        let studentID = element.id.replace('progress_box_', '');
        let currentPeriod = parseInt(element.getAttribute('target_period'));
        return {"id": studentID, "currentPeriod": currentPeriod, "element": element};
    });
    let apiPool = new APIpooler();
    processed.forEach(data => {
        apiPool.registerCallbackInQueue(
            () => fetchStudentProgressSummary(data.element, data.id, data.currentPeriod)
        );
    });
    apiPool.processQueue()
    .then(() => {
        let sortedBySeverityHighToLow = Object.entries(studentData).sort((a, b) => b[1].points_behind - a[1].points_behind);
        for(let [id, entry] of sortedBySeverityHighToLow){
            apiPool.registerCallbackInQueue(
                () => PrefetchStudentResults(id)
            );
        }
        return apiPool.processQueue();
    });
});


