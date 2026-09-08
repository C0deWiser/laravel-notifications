<?php

namespace Codewiser\Notifications\Messages;

use Codewiser\Notifications\Contracts\MessageContract;
use Codewiser\Notifications\Enumerations\MessageLevel;
use Codewiser\Notifications\Traits\AsWebNotification;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Tappable;

class DatabaseMessage extends \Illuminate\Notifications\Messages\DatabaseMessage implements Arrayable, MessageContract, Renderable
{
    use Tappable, AsWebNotification;

    /**
     * The notification cannot be marked as read by the user.
     *
     * You may describe a reason why it is persistent.
     */
    public function persistent(bool|string $persistent = true): static
    {
        return $this->setOptionData('persistent', $persistent);
    }

    /**
     * Check if the notification is persistent and get a description (optional).
     *
     * @deprecated Moved to WebNotification
     */
    public function isPersistent(): bool|string
    {
        return Arr::get($this->data, 'options.data.persistent', false);
    }

    /**
     * @param  string|MessageLevel  $level
     *
     * @return $this
     */
    public function level($level): static
    {
        if (is_string($level)) {
            $this->setOptionData('level', $level);

            if ($level == 'error') {
                $level = 'danger';
            }

            $level = MessageLevel::tryFrom($level) ?? $level;
        }

        if ($level instanceof MessageLevel) {
            $this->setOptionData('level', $level->value);
            $this->priority($level->priority());
        }

        return $this;
    }

    /**
     * Set the message priority. The higher the value, the more important the message.
     *
     * @param  int  $priority
     *
     * @return $this
     */
    public function priority(int $priority): static
    {
        $this->setOptionData('priority', $priority);

        return $this;
    }

    /**
     * Attach a model to the notification.
     */
    public function attach(Model $model): static
    {
        $bindings = $this->getOptionData("bind.{$model->getMorphClass()}") ?? [];

        $bindings[] = $model->getKey();

        return $this->setOptionData("bind.{$model->getMorphClass()}", $bindings);
    }

    /**
     * Bind notification to a model.
     *
     * @deprecated Use attach() instead.
     */
    public function bindTo(Model $model): static
    {
        return $this->attach($model);
    }

    /**
     * Get models mentioned in the notification.
     *
     * @deprecated Moved to WebNotification
     */
    public function mentions(): Collection
    {
        $binds = Arr::get($this->data, 'options.data.bind') ?? [];
        $morphMap = Relation::morphMap();
        $mentions = collect();

        foreach ($binds as $morph => $keys) {

            $model = $morphMap[$morph] ?? $morph;

            if (class_exists($model) && method_exists($model, 'query')) {
                // Keys may be a single id (old format) or an array of ids.
                $models = $model::query()->find($keys);
                if ($models) {
                    $mentions = $mentions->merge(is_iterable($models) ? $models : [$models]);
                }
            }
        }

        return $mentions;
    }

    /**
     * @deprecated Use mentions() instead.
     */
    public function bindedTo(): ?Model
    {
        return $this->mentions()->first();
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function render(): string
    {
        return '<html lang="'.app()->getLocale().'">
<head></head>
<body>
<div style="width: 100%; height: 90vh; display: flex; justify-content: center; align-items: center;">
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 2200 2200" style="enable-background:new 0 0 2200 2200; width: 256px; height: 256px" xml:space="preserve">
<g id="background">
	<rect style="fill:#FFFFFF;" width="2200" height="2200"/>
</g>
<g id="Objects">
	<g>
		<path style="fill:#009EFF;" d="M1249.252,1694.816c0,105.097-85.198,190.294-190.294,190.294s-190.294-85.198-190.294-190.294
			H1249.252z"/>
		<path style="fill:#009EFF;" d="M1718.642,1565.357c0,51.507-41.746,93.241-93.241,93.241H492.52
			c-51.495,0-93.241-41.734-93.241-93.241c0-17.691,4.988-34.599,13.953-49.057c8.953-14.484,21.871-26.492,37.705-34.384
			c33.665-16.782,61.142-42.365,80.184-73.138c19.067-30.798,29.7-66.799,29.7-104.416V939.075
			c0-234.655,162.262-431.416,380.717-484.198c-0.556-4.571-0.821-9.218-0.821-13.928c0-65.309,52.934-118.243,118.243-118.243
			c65.296,0,118.243,52.934,118.243,118.243c0,4.71-0.278,9.357-0.833,13.928c14.964,3.611,29.674,7.905,44.07,12.829
			c-19.017,41.077-29.624,86.839-29.624,135.088c0,177.832,144.167,321.999,321.999,321.999c14.913,0,29.599-1.01,43.969-2.98
			c0.202,5.733,0.303,11.478,0.303,17.262v365.286c0,75.247,42.567,143.99,109.896,177.554c15.822,7.892,28.74,19.901,37.705,34.384
			C1713.642,1530.758,1718.642,1547.666,1718.642,1565.357z"/>
		<g>
			<circle style="fill:#E52323;" cx="1512.815" cy="602.795" r="287.905"/>
			<polygon style="fill:#FFFFFF;" points="1483.381,745.533 1483.381,526.416 1448.293,544.677 1425.475,497.488 1495.512,460.056 
				1542.338,460.056 1542.338,745.533 			"/>
		</g>
	</g>
</g>
</svg>
</div>
</body>
</html>';
    }
}
