<?php

return [
    /*
     | Path to the Python executable.
     | Change if using a virtualenv: e.g. /home/user/.venv/bin/python3
     */
    'python_bin' => env('OCR_PYTHON_BIN', 'python3'),

    /*
     | PaddleOCR language model.
     | 'en'  — English (also extracts printed numbers from any script)
     | 'ta'  — Tamil
     | 'ch'  — Simplified Chinese (works for dense scripts)
     | For Tamil+English mixed bills 'en' is a good starting point.
     | Set OCR_LANGUAGE=ta in .env for Tamil-primary bills.
     */
    'language' => env('OCR_LANGUAGE', 'en'),

    /*
     | Max seconds to wait for the OCR process before killing it.
     */
    'timeout' => (int) env('OCR_TIMEOUT', 120),

    /*
     | Path to the Python extraction script.
     */
    'script_path' => env('OCR_SCRIPT_PATH', storage_path('app/ocr/invoice_ocr.py')),
];
