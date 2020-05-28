import {useEffect} from 'react';
import {Subscribable} from 'rxjs/internal/types';

export default function useSubscribe<T>(subject: Subscribable<T>, observer: (value: T) => void) {
	useEffect(() => {
		const sub = subject.subscribe(observer);
		return () => sub.unsubscribe();
	});
}
