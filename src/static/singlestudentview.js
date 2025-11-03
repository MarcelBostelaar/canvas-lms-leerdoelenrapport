function onlyalfanumeric(string){
    return string.replace(/[^a-zA-Z0-9]/gi, '');
}



function createResultMarkers(label, data, colorClass){
    for (let leerdoelnaam in data) {
        let rij = document.getElementById("leerdoel_" + onlyalfanumeric(leerdoelnaam));
        let teLabelen = rij
            .querySelectorAll(".toetsniveau_" + data[leerdoelnaam]["niveau"].toString() + ".last")[0];

        if(teLabelen == undefined){
            console.warn("Could not find cell for " + leerdoelnaam + " with level " + data[leerdoelnaam]["niveau"]);
            continue;
        }

        let newElement = document.createElement("div");
        newElement.classList.add("controller_" + onlyalfanumeric(label));
        newElement.classList.add("generated");
        newElement.classList.add("niveau_indicator");
        newElement.classList.add(colorClass);
        addMHEvents(newElement, colorClass);

        teLabelen.appendChild(newElement);
    };
}

function createMarkerSelector(groupID, label, colorClass, toggleCallback){
    let idGenerated = "collection" + onlyalfanumeric(label);
    let labelele = document.createElement("label");
    labelele.setAttribute("for", idGenerated);
    labelele.classList.add(colorClass);
    labelele.innerHTML = label;
    let input = document.createElement("input");
    input.setAttribute("id", idGenerated);
    input.setAttribute("type", "checkbox");
    input.setAttribute("checked", true);
    input.onchange = toggleCallback;

    addMHEvents(labelele, colorClass);
    addMHEvents(input, colorClass);

    // get the element in the group where the markers go.
    // console.log
    let form = document.querySelector("#" + groupID + " .marker_container");
    form.append(input);
    form.append(labelele);
    form.append(document.createElement("br"));
}

function deleteGeneratedMarkers(){
    Array.from(document.getElementsByClassName("generated")).forEach(item => {
        item.remove();
    });
}

function toggleShow(item){
    item["show"] = !item["show"];
    refreshMarkers();
}

function toggleSupercheck(groupElement){
    let self = groupElement.querySelector(".supercheck");
    let checkValue = self.checked;
    let allboxes = groupElement.querySelectorAll('input[type=checkbox]');
    [...allboxes].forEach(box=> {
        if(box.checked != checkValue){
            box.checked = checkValue;
            box.onchange();
        }
    });
}

function refreshMarkers(){
    deleteGeneratedMarkers();
    for (let [groupname, group] of Object.entries(resultaten)) {
        group.forEach(container => {
            if(container["show"]){
                createResultMarkers(container["label"], container["data"], container["color_class"])
            }
        });
    }
}


//Highlighting functionality
const hm = "highlight_marker";
const hm_fade = "highlight_marker_fade"
function onMarkerUnhover(){
    Array.from(document.getElementsByClassName(hm))
    .forEach(item => item.classList.remove(hm));
    Array.from(document.getElementsByClassName(hm_fade))
    .forEach(item => item.classList.remove(hm_fade))
}

function onMarkerHover(color_class){
    onMarkerUnhover();
    Array.from(document.getElementsByClassName(color_class))
    .forEach(item =>{
        item.classList.add(hm);
    });
    Array.from(document.getElementsByClassName("niveau_indicator"))
    .forEach(item =>{
        if(item.classList.contains(hm)){
            return;
        }
        item.classList.add(hm_fade);
    });
}

function addMHEvents(item, colorClass){
    item.onmouseover = () => onMarkerHover(colorClass);
    item.onmouseleave = () => onMarkerUnhover();
}

function refresh(){
    

    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    // let id = getQueryVariable("id");
    fetch(`/controllers/ClearCacheController.php?studentID=${id}`)
    .then(_ => {
        console.log("Cache cleared, refreshing page");
        window.location.reload();
    });
}

function createGroup(groupname){
    let group = document.querySelector(".marker_group, .html_template").cloneNode(true);
    group.classList.remove("html_template");
    let title = group.querySelector(".title");
    title.innerHTML = groupname;
    let supercheck = group.querySelector("input");
    supercheck.onchange = () => toggleSupercheck(group);
    let groupID = "group_" + onlyalfanumeric(groupname);
    group.id = groupID;
    return [group, groupID];
}

let timeoutDatechange = null;

//Startup
document.addEventListener("DOMContentLoaded", ()=> {
    document.getElementById("date").addEventListener('input', function (evt) {
        clearTimeout(timeoutDatechange);
        timeoutDatechange = setTimeout(function(){
            const dateVal = evt.target.value;
            const url = new URL(window.location.href);
            url.searchParams.set('date', dateVal);
            window.location.href = url.toString();
        }, 3000);
    });

    let colorIndex = 0;
    let newResultaten = [];
    for(let group in resultaten){
        newResultaten[group] = [];
        let form = document.getElementById("resultaten_form");
        let items = createGroup(group);
        form.appendChild(items[0]);
        let groupID = items[1];

        for (let label in resultaten[group]){
            let colorClass = "color_" + colorIndex.toString();
            let i = {
                "label" : label,
                "color_class" : colorClass,
                "data": resultaten[group][label],
                "show": true
            };
            newResultaten[group].push(i);
            let toggleCallback = () => toggleShow(i);
            createMarkerSelector(groupID, label, colorClass, toggleCallback);
            colorIndex++;
        }
    }
    resultaten = newResultaten;
    refreshMarkers();
});