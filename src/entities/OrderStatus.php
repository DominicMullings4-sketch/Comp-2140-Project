<?php

/**
 * OrderStatus: enumeration from the EntityClasses diagram.
 */
enum OrderStatus: string
{
    case PendingConfirmation = 'PendingConfirmation';
    case OrderConfirmed = 'OrderConfirmed';
    case Cancelled = 'Cancelled';
}
