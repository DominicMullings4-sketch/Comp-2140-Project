<?php

/**
 * ReportType: enumeration from the EntityClasses diagram.
 */
enum ReportType: string
{
    case SalesSummary = 'SalesSummary';
    case DeliverySummary = 'DeliverySummary';
    case LowStock = 'LowStock';
}
