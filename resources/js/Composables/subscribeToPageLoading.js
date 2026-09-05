/**
 * Refleja el ciclo de las visitas de Inertia en un único estado global.
 *
 * @param {import('@inertiajs/vue3').router} inertiaRouter
 * @param {(isLoading: boolean) => void} onLoadingChange
 * @returns {() => void}
 */
export function subscribeToPageLoading(inertiaRouter, onLoadingChange) {
    const removeStartListener = inertiaRouter.on('start', () => {
        onLoadingChange(true);
    });

    const removeFinishListener = inertiaRouter.on('finish', () => {
        onLoadingChange(false);
    });

    return () => {
        removeStartListener();
        removeFinishListener();
    };
}
