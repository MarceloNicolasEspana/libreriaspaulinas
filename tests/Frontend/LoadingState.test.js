import assert from 'node:assert/strict';
import test from 'node:test';
import { subscribeToPageLoading } from '../../resources/js/Composables/subscribeToPageLoading.js';

function createRouterFake() {
    const listeners = new Map();

    return {
        emit(event) {
            listeners.get(event)?.();
        },
        has(event) {
            return listeners.has(event);
        },
        on(event, listener) {
            listeners.set(event, listener);

            return () => listeners.delete(event);
        },
    };
}

test('shows while an Inertia visit is loading and hides when it finishes', () => {
    const router = createRouterFake();
    const loadingStates = [];
    subscribeToPageLoading(router, (isLoading) => loadingStates.push(isLoading));

    router.emit('start');
    router.emit('finish');

    assert.deepEqual(loadingStates, [true, false]);
});

test('removes both Inertia listeners when the subscription is disposed', () => {
    const router = createRouterFake();
    const removeListeners = subscribeToPageLoading(router, () => {});

    removeListeners();

    assert.equal(router.has('start'), false);
    assert.equal(router.has('finish'), false);
});
