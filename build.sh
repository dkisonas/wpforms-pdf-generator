#!/bin/bash

# Define your plugin directory and ZIP file name
PLUGIN_DIR="./"  # Assuming the script is located in the root of your plugin directory
ZIP_FILE="custom-pdf-plugin.zip"

# Create the ZIP file excluding the .idea directory
zip -r $ZIP_FILE . -x "*.idea*"

# Output the result
echo "Plugin ZIP file created: $ZIP_FILE"