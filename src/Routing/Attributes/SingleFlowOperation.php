<?php

namespace Suphle\Routing\Attributes;

enum SingleFlowOperation: string
{
    case ALTERS_QUERY = 'altersQuery'; // Query updating operation
} 