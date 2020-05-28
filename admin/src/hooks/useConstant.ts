import {useRef} from 'react';

// https://github.com/Andarist/use-constant/blob/master/src/index.ts
export default function useConstant<T>(fn: () => T): T {
	const ref = useRef<{v: T}>();
	if (!ref.current) ref.current = {v: fn()};
	return ref.current.v;
}