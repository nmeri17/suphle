<?php

namespace Suphle\Routing\Attributes;

enum CollectionFlowOperation: string
{
    case PIPE_TO = 'pipeTo';           // Iterative operation
    case AS_ONE = 'asOne';             // Concatenated indexes
    case RANGE = 'inRange';         // Contrasting indexes (numeric)
    case SET_FROM_SERVICE = 'setFromService'; // Custom service
} 