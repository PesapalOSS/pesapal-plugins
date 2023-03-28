#!/bin/bash

# Navigate to the parent directory of the sub-folders
cd packages

# Loop through each sub-folder
for folder in */; do
  folder=${folder%/} # Remove the trailing "/" character
  zip -r "${folder}.zip" "$folder" # Compress the folder and save as ZIP file
done
