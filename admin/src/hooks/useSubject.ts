import { useState, useEffect } from 'react';
import { Subscribable } from 'rxjs/internal/types';

type IMessagerSubject<T> = Subscribable<T> & { getValue: () => T };

export default function useSubject<T>(subject: IMessagerSubject<T>) {
	const [value, setValue] = useState(subject.getValue());

	useEffect(() => {
		const sub = subject.subscribe(next => setValue(next));
		setValue(subject.getValue());
		return () => sub.unsubscribe();
	}, [subject]);

	return value;
}
