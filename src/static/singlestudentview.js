function onlyalfanumeric(string){
    return string.replace(/[^a-zA-Z0-9]/gi, '');
}

function createResultMarkers(label){
    let data = resultaten[label];
    data.forEach((element, leerdoelnaam) => {
        let rij = document.getElementById("leerdoel_" + onlyalfanumeric(leerdoelnaam));
        let teLabelen = rij
            .getElementsByClassName("toetsniveau_" + element["niveau"].toString())
            .getElementsByClassName("last")[0];
        let newElement = new HTMLDivElement();
        newElement.classList.add("controller_" + onlyalfanumeric(label));
        teLabelen.children.add(newElement);
        newElement.innerHTML = "HDSJJFKDS";
    });
}

document.addEventListener("DOMContentLoaded", ()=> {
    resultaten.forEach((_, index) => createResultMarkers(index));
});