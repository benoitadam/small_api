import axios, { AxiosRequestConfig, AxiosPromise, AxiosInstance } from 'axios';
import Config from '../config';
const TIMEOUT = 8000;

export interface ApiRequest {
	type: 'PUT' | 'POST' | 'GET';
	url: string;
	data: any;
	config: AxiosRequestConfig;
}

export interface ApiResult {
	success: boolean;
	request: ApiRequest;
	response: any | undefined;
	data: any | undefined;
	error: { message: string } | undefined;
}

export const asArray = (t: any) => (Array.isArray(t) ? t : [t]);

class Api {
	axios: AxiosInstance = axios.create({
		baseURL: Config.API_BASE_URL,
		timeout: TIMEOUT,
		headers: {},
	});
	sessionId: string = '';

	setSessionId(sessionId: string | null) {
		this.axios = axios.create({
			baseURL: Config.API_BASE_URL,
			timeout: TIMEOUT,
			headers: {
				session: sessionId,
			},
		});
		if (sessionId) this.sessionId = sessionId;
	}

	_promise(request: any, promise: AxiosPromise<any>): Promise<ApiResult> {
		console.debug('Api send', request);
		return promise
			.then(response => {
				console.debug('Api success', request, response);
				const { data } = response;
				if (data.status !== 'error') {
					return {
						success: true,
						request,
						response,
						data,
					} as ApiResult;
				} else {
					return {
						success: false,
						request,
						response,
						error: {
							message: data.error_message,
						},
					} as ApiResult;
				}
			})
			.catch(response => {
				console.error('Api error', request, response);
				return {
					success: false,
					request,
					response,
					error: {
						message: response.message,
					},
				} as ApiResult;
			})
			.then(result => {
				return result;
			});
	}

	get(url: string, config?: AxiosRequestConfig) {
		return this._promise({ type: 'GET', url, config }, this.axios.get(url, config));
	}

	post(url: string, data?: any, config?: AxiosRequestConfig) {
		return this._promise({ type: 'POST', url, data, config }, this.axios.post(url, data, config));
	}

	syncPost(url: string, data?: any): any {
		let httpRequest = new XMLHttpRequest();
		httpRequest.open('POST', Config.API_BASE_URL + url, false);
		httpRequest.setRequestHeader('Content-Type', 'application/json');
		httpRequest.setRequestHeader('session', this.sessionId);
		if (data) {
			httpRequest.send(JSON.stringify(data));
		} else {
			httpRequest.send();
		}
		console.debug(httpRequest.responseText);
		return {
			success_status: httpRequest.status,
			result: httpRequest.responseText,
		};
	}

	put(url: string, data?: any, config?: AxiosRequestConfig) {
		return this._promise({ type: 'PUT', url, data, config }, this.axios.put(url, data, config));
	}
}

export default new Api();
