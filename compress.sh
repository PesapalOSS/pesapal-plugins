#!/bin/bash

# Navigate to the parent directory of the sub-folders
cd packages

# Loop through each sub-folder
for folder in */; do
  folder=${folder%/} # Remove the trailing "/" character
  tar -czvf "${folder}.tar.gz" "$folder" # Compress the folder and save as tar.gz file
done
