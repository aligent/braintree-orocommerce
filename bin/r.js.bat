@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../web/bundles/npmassets/requirejs/bin/r.js
node "%BIN_TARGET%" %*
