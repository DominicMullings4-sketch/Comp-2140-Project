<?php

/**
 * UserRole: enumeration from the EntityClasses diagram.
 */
enum UserRole: string
{
    case BusinessOwner = 'BusinessOwner';
    case OfficeStaff = 'OfficeStaff';
    case DeliveryWorker = 'DeliveryWorker';
}
