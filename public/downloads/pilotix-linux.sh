#!/bin/bash
# Launcher PILOTIX Desktop pour Linux (.sh)

URL="http://localhost:8000"
echo "Lancement de PILOTIX Desktop..."

if command -v electron &> /dev/null; then
    electron --app="$URL" --name="PILOTIX" &
elif command -v google-chrome &> /dev/null; then
    google-chrome --app="$URL" --name="PILOTIX" --user-data-dir="/tmp/PILOTIX-desktop" &
elif command -v chromium-browser &> /dev/null; then
    chromium-browser --app="$URL" --name="PILOTIX" --user-data-dir="/tmp/PILOTIX-desktop" &
elif command -v chromium &> /dev/null; then
    chromium --app="$URL" --name="PILOTIX" --user-data-dir="/tmp/PILOTIX-desktop" &
elif command -v firefox &> /dev/null; then
    firefox --new-window "$URL" &
else
    xdg-open "$URL" &
fi
