# Required data

The tool reads all the outcomes from a course. It uses the hierarchy and grouping created in Canvas.
It reads further data per outcome from the json file (currently stored on disk, in future per course?), in which you define Toetsmomenten in an array of arrays, in which you list periods where it is tested. And Beschrijvingen in an array. The zeroth element is the first tested level.