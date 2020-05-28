import {useRef, useCallback, useEffect} from 'react';

// https://github.com/xnimorz/use-debounce
export default function useDebouncedCallback<T extends (...args: any[]) => any>(
	callback: T,
	delay: number,
	options: {maxWait?: number; leading?: boolean} = {}
): [T, () => void, () => void] {
	const maxWait = options.maxWait;
	const maxWaitHandler = useRef(null);
	const maxWaitArgs: {current: any[]} = useRef([]);

	const leading = options.leading;
	const wasLeadingCalled: {current: boolean} = useRef(false);

	const functionTimeoutHandler = useRef(null);
	const isComponentUnmounted: {current: boolean} = useRef(false);

	const debouncedFunction = useRef(callback);
	debouncedFunction.current = callback;

	const cancelDebouncedCallback: () => void = useCallback(() => {
		clearTimeout(functionTimeoutHandler.current as any);
		clearTimeout(maxWaitHandler.current as any);
		maxWaitHandler.current = null;
		maxWaitArgs.current = [];
		functionTimeoutHandler.current = null;
		wasLeadingCalled.current = false;
	}, []);

	useEffect(
		() => () => {
			// we use flag, as we allow to call callPending outside the hook
			isComponentUnmounted.current = true;
		},
		[]
	);

	const debouncedCallback = useCallback(
		(...args) => {
			maxWaitArgs.current = args;
			clearTimeout(functionTimeoutHandler.current as any);

			if (!functionTimeoutHandler.current && leading && !wasLeadingCalled.current) {
				debouncedFunction.current(...args);
				wasLeadingCalled.current = true;
				return;
			}

			(functionTimeoutHandler as any).current = setTimeout(() => {
				cancelDebouncedCallback();

				if (!isComponentUnmounted.current) {
					debouncedFunction.current(...args);
				}
			}, delay);

			if (maxWait && !maxWaitHandler.current) {
				(maxWaitHandler as any).current = setTimeout(() => {
					const args = maxWaitArgs.current;
					cancelDebouncedCallback();

					if (!isComponentUnmounted.current) {
						debouncedFunction.current.apply(null, args);
					}
				}, maxWait);
			}
		},
		[maxWait, delay, cancelDebouncedCallback, leading]
	);

	const callPending = () => {
		// Call pending callback only if we have anything in our queue
		if (!functionTimeoutHandler.current) {
			return;
		}

		debouncedFunction.current.apply(null, maxWaitArgs.current);
		cancelDebouncedCallback();
	};

	// At the moment, we use 3 args array so that we save backward compatibility
	return [debouncedCallback as T, cancelDebouncedCallback, callPending];
}
