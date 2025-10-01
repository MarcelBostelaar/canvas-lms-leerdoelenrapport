Caching of methods is done via the caching utilities. It is implemented on the services using a subclass which uses the decorator pattern to add caching. This way, caching is logically seperated from the core functionality, and can easily be disable by commenting out the decorated override, or renaming the class back to the uncached version.

Caching is done through caching rules, of which there are three types:

 * MaximallyRestrictive - This info is cached on a per-user or per-api-key basis.
 * StudentIDRestricted - This info is restricted to only the users who have shown to be able to access data bound to that specific user id before. This way, commonly accesible data can be shared between faculty members and students alike, improving performance. Additionally, it is restricted on a course by course basis.
 * CourseRestricted - This info is restricted to all members of the course. This is data such as outcome information or plannings.

The caching rules are statefull objects that need to be created anew for each cachable request. They are used to generate a cache key from the function, the calling object and the arguments, following the specific logic of each restriction level.