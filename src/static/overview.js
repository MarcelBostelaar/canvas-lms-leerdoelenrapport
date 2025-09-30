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


function populateProgressBox(progress_box, data){
    let text = '';
    text += "On track: " + (data.on_track + data.exceeded).toString() + " / " + data.total.toString();
    text += "<br>Aantal niveaus achterstand: " + data.points_behind.toString();
    text += " - Aantal leerdoelen voorsprong: " + data.exceeded.toString();

    progress_box.innerHTML = text;

    progress_box.style.backgroundColor = calculateRGBFromScore(1 - (data.points_behind / data.total_points_needed));
}

function fetchStudentProgressSummary(progress_box, studentID, currentPeriod){
    return fetch(`/controllers/api/StudentProgressSummary.php?studentID=${studentID}&currentPeriod=${currentPeriod}`)
    .then(response => response.json())
    .then(data => {
        populateProgressBox(progress_box, data);
    })
    .catch(error => {
        console.error('Error fetching student progress summary:', error);
        progress_box.innerHTML = 'Error';
    });
}

document.addEventListener("DOMContentLoaded", function() {
    let elementsToLoad = document.querySelectorAll('.progress_box');
    elementsToLoad.forEach(element => {
        let studentID = element.id.replace('progress_box_', '');
        let currentPeriod = parseInt(element.getAttribute('target_period'));
        registerCallbackInQueue(() => fetchStudentProgressSummary(element, studentID, currentPeriod));
    });
    processQueue();
});


