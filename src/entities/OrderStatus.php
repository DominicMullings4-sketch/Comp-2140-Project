<?php

/**
 * OrderStatus: enumeration from the EntityClasses diagram.
 */
enum OrderStatus: string
{
    case PendingConfirmation = 'PendingConfirmation';
    case OrderConfirmed = 'OrderConfirmed';
    case OutForDelivery = 'OutForDelivery';
    case Cancelled = 'Cancelled';
}
