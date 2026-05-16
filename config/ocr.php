<?php

return [
    /*
     | Path to the Python executable.
     | Change if using a virtualenv: e.g. /home/user/.venv/bin/python3
     */
    'python_bin' => env('OCR_PYTHON_BIN', 'python3'),

    /*
     | Tesseract language.
     | 'en'    — English
     | 'ta'    — Tamil
     | 'ta+en' — Tamil + English (mixed bills)
     | Requires: sudo apt install tesseract-ocr-tam for Tamil support.
     */
    'language' => env('OCR_LANGUAGE', 'en'),

    /*
     | Max seconds to wait for the OCR process before killing it.
     */
    'timeout' => (int) env('OCR_TIMEOUT', 60),

    /*
     | Path to the Python extraction script.
     */
    'script_path' => env('OCR_SCRIPT_PATH', storage_path('app/ocr/invoice_ocr.py')),
];
