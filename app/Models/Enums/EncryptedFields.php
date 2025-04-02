<?php

namespace App\Models\Enums;

enum EncryptedFields : string
{
    case AES_KEYS = "aes_keys";
    case BASE64_AUTH = "base64_auth";
}