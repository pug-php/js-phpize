myVar = 9;
if (isset(myVar)) {
    unset(myVar);
}
if (!isset(myVar)) {
    myVar = 10;
}

return myVar;