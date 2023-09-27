<?php

namespace Codewiser\Notifications\Enumerations;

enum MessageLevel: string
{
    # ⬛
    case primary = 'primary';

    # ⬜
    case secondary = 'secondary';

    # 🟦
    case info = 'info';

    # 🟩
    case success = 'success';

    # 🟨
    case warning = 'warning';

    # 🟥
    case danger = 'danger';

    public function priority(): int
    {
        return match ($this) {
            self::danger => 10,
            self::warning => 8,
            self::info => 6,
            self::success => 4,
            self::primary => 2,
            self::secondary => 0,
        };
    }
}
