<?php declare(strict_types=1);

namespace IFR\Logistica;

final readonly class IrisRateLimit
{
    /**
     * Agregar un margen a la cantidad máxima de solicitudes para que no llegue hasta el Top.
     * @var int
     */
    private int $MARGIN_MAX_NUM_REQUESTS;
    private array $rate_split;

    public function __construct(
        private int    $remaining,
        private string $policy,
    )
    {
        $this->MARGIN_MAX_NUM_REQUESTS = 3;
        $this->rate_split = explode(';', $this->policy, 3);
    }

    /**
     * Retorna la cantidad de segundos que necesita esperar para la próxima solicitud.
     * @return int
     */
    public function sleep(): int
    {
        return $this->canAwait() ? $this->getReset() : 0;
    }

    /**
     * Retorna true si se necesita esperar tiempo (getReset) antes de volver a tener la cantidad máxima de solicitudes permitidas.
     * @return bool
     */
    private function canAwait(): bool
    {
        $current_requests = $this->getPolicyMaximum() - ($this->getPolicyMaximum() < $this->MARGIN_MAX_NUM_REQUESTS ? 0 : $this->MARGIN_MAX_NUM_REQUESTS);

        return $current_requests == $this->getAvailableRequests();
    }

    /**
     * Retorna la cantidad máxima de solicitudes por ventana de tiempo.
     * @return int cantidad máxima
     */
    private function getPolicyMaximum(): int
    {
        return (int)$this->rate_split[0];
    }

    /**
     * Retorna "la cantidad máxima de solicitudes permitida"
     * menos
     * "la cantidad de solicitudes disponibles en la ventana de tiempo (remaining)".
     * @return int
     */
    private function getAvailableRequests(): int
    {
        return $this->getPolicyMaximum() - $this->remaining;
    }

    /**
     * Retorna la cantidad de segundos que se deben esperar antes de volver a tener la cantidad máxima de solicitudes permitidas.
     * @return int
     * @see https://github.com/yiisoft/yii2/blob/master/framework/filters/RateLimiter.php#L137 Yii RateLimiter.
     */
    private function getReset(): int
    {
        return (int)ceil(
            ($this->getAvailableRequests() + 1) *
            ($this->getPolicyTime() / $this->getPolicyMaximum())
        );
    }

    /**
     * Retorna la duración de la ventana de tiempo en segundos.
     * @return int duración en segundos
     */
    private function getPolicyTime(): int
    {
        return (int)ltrim($this->rate_split[1], "w=");
    }
}
