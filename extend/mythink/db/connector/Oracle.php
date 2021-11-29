<?php
declare (strict_types = 1);

namespace mythink\db\connector;

class Oracle extends \think\db\connector\Oracle
{
    use ConnectionTrait;
    use PDOConnectionTrait;
}
