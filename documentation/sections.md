# Expections

All students should be sorted into sections.

The tool automatically processes sectionnames. It splits sections on dashes (-).
It then uses the environment variable "yearPositionInSectionName" (zero indexed) to get the year designation of the student, stripping away the letters.

For example, sections: "1A - 2024/2025", "2A - 2024/2025", "1B - 2024/2025", "1B - 2023/2025"