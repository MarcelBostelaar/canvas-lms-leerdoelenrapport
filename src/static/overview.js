// const JSZip = require("jszip");

function rescaleValueToRange(value, originalMin, originalMax, newMin, newMax) {
    return ((value - originalMin) / (originalMax - originalMin)) * (newMax - newMin) + newMin;
}

function calculateRGBFromScore(score) {
    // Score 0 = green, Score 15+ = red
    if (score === 0) {
        return 'RGB(0,255,0)'; // Green
    }
    
    const maxScore = 15;
    const clampedScore = Math.min(score, maxScore); // Cap at 15
    const normalizedScore = clampedScore / maxScore; // 0 to 1
    
    const exponent = 1;
    const adjustedScore = normalizedScore ** exponent; // Non-linear scale
    
    // Green: 255 at score 0, down to ~55 at score 15
    let green = Math.floor(rescaleValueToRange(adjustedScore, 0, 1, 255, 0));
    
    // Red: 55 at score 0, up to 255 at score 15
    let red = Math.floor(rescaleValueToRange(adjustedScore, 0, 1, 0, 255));
    
    console.log(`Score: ${score}, Red: ${red}, Green: ${green}`);
    return `RGB(${red},${green},0)`;
}

let studentData = {};

function populateProgressBox(progress_box, data){
    console.log("Populating progress box with data:", data);
    $isOnTrack = data.on_track + data.exceeded >= data.total;
    let text = '';
    text += "On track: " + (data.on_track + data.exceeded).toString() + " / " + data.total.toString();
    text += "<br>Aantal niveaus achterstand: " + data.points_behind.toString();
    text += " - Aantal leerdoelen voorsprong: " + data.exceeded.toString();

    progress_box.innerHTML = text;
    progress_box.style.backgroundColor = calculateRGBFromScore(data.points_behind);
    progress_box.classList.add($isOnTrack ? "onTrack" : "behind");
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

function sleep(ms){
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function getHtmlPage(url){
    try {
        const resp = await fetch(url);
        const html = await resp.text();

        // const parser = new DOMParser();
        // const doc = parser.parseFromString(html, 'text/html');

        //put in iframe, let it render, then retrieve it.
        let frame = document.createElement("iframe");
        frame.sandbox = "allow-scripts allow-same-origin";
        frame.style.position = "absolute";
        frame.style.left = "-9999px";
        frame.style.width = "1920px";
        frame.style.height = "1080px";
        document.body.appendChild(frame);
        
        // Wait for iframe to load properly
        await new Promise((resolve, reject) => {
            frame.onload = resolve;
            frame.onerror = reject;
            frame.srcdoc = html;
        });

        await sleep(2000);

        const doc = frame.contentDocument || frame.contentWindow.document;
        
        // Ensure document exists
        if (!doc || !doc.documentElement) {
            throw new Error('Failed to load iframe content');
        }
        
        // Clone the entire HTML document (including head and body)
        const content = doc.documentElement.cloneNode(true);

        // Get dimensions from the document body or documentElement
        let width = Math.max(
            doc.body?.scrollWidth || 0,
            doc.body?.offsetWidth || 0,
            doc.documentElement?.scrollWidth || 0,
            doc.documentElement?.offsetWidth || 0
        );
        let height = Math.max(
            doc.body?.scrollHeight || 0,
            doc.body?.offsetHeight || 0,
            doc.documentElement?.scrollHeight || 0,
            doc.documentElement?.offsetHeight || 0
        );
        
        // console.log(`Page dimensions: ${width}x${height}`);
        
        // Clean up the iframe
        document.body.removeChild(frame);
        
        return {
            node: content,
            width: width,
            height: height
        };
    } catch (err) {
        console.error('getHtmlPage error fetching/parsing:', err);
        throw err;
    }
}

async function downloadAllPdfFiles(){
    //get all urls
    let pages = await Promise.all(Array.from(document.getElementsByTagName("li"))
    .map(async x => {
        let element = await getHtmlPage(Array.from(x.querySelectorAll("a"))[0].href);
        return {
            name: x.firstChild.firstChild.textContent,
            class: x.parentElement.previousSibling.innerText,
            element: element.node,
            width: element.width,
            height: element.height
        }
    }));

    const zip = new JSZip();

    for (let i = 0; i < pages.length; i++) {
        const page = pages[i];
        let fileName = page.class + " " + page.name + `.pdf`;
        fileName = fileName.replaceAll("/", "-");
        fileName = fileName.replaceAll(/\s+/g, " ");
        console.log(fileName);
        
        // Get the dimensions of the page element
        const width = page.width;
        const height = page.height;
        // console.log(width, height);
        const pdfBlob = await html2pdf()
        .from(page.element)
        .set({
            margin: 0,
            filename: fileName,
            html2canvas: { scale: 2 },
            jsPDF: { 
                unit: "px", 
                format: [width, height], 
                orientation: width > height ? "landscape" : "portrait",
                hotfixes: ["px_scaling"]
            }
        })
        .outputPdf("blob");

        zip.file(fileName, pdfBlob);
    }

    const content = await zip.generateAsync({ type: "blob" });
    saveAs(content, "rapporten.zip");
}


let timeoutDatechange = null;

document.addEventListener("DOMContentLoaded", function() {
    // .addEventListener("on")
    document.getElementById("date").addEventListener('input', function (evt) {
        clearTimeout(timeoutDatechange);
        timeoutDatechange = setTimeout(function(){
            const dateVal = evt.target.value;
            const url = new URL(window.location.href);
            url.searchParams.set('date', dateVal);
            window.location.href = url.toString();
        }, 3000);
    });

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


