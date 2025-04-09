<?php

namespace App;

enum OrderStatus: string
{
    case ORDERED = 'ordered';
    case COMPLETED = 'completed';
    case PROCESSING = 'processing';
}
