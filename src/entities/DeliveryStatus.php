<?php

/**
 * DeliveryStatus: enumeration from the EntityClasses diagram.
 */
enum DeliveryStatus: string
{
    case NotScheduled = 'NotScheduled';
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Failed = 'Failed';
    case Cancelled = 'Cancelled';
}
