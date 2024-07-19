#!/bin/bash

# Define your plugin directory and ZIP file name
ZIP_FILE="custom-pdf-plugin.zip"

# Create the ZIP file excluding the .idea directory
zip -r $ZIP_FILE . -x "*.idea*"

# Output the result
echo "Plugin ZIP file created: $ZIP_FILE"