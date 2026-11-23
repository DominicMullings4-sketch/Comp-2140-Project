<?php

/**
 * OrderSource: enumeration from the EntityClasses diagram.
 */
enum OrderSource: string
{
    case Facebook = 'Facebook';
    case Instagram = 'Instagram';
    case TikTok = 'TikTok';
    case Twitter = 'Twitter';
    case WhatsApp = 'WhatsApp';
    case Other = 'Other';
}
