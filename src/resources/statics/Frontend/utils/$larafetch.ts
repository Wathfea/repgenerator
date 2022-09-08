import { $fetch, FetchOptions, FetchError } from "ohmyfetch";

const CSRF_COOKIE = "XSRF-TOKEN";
const CSRF_HEADER = "X-XSRF-TOKEN";

// Unfortunately could not import these types from ohmyfetch, so copied them here
interface ResponseMap {
    blob: Blob;
    text: string;
    arrayBuffer: ArrayBuffer;
}
type ResponseType = keyof ResponseMap | "json";
// end of copied types

type LarafetchOptions<R extends ResponseType> = FetchOptions<R> & {
    redirectIfNotAuthenticated?: boolean;
    redirectIfNotVerified?: boolean;
};

export async function $larafetch<T, R extends ResponseType = "json">(
    path: RequestInfo,
    {
        redirectIfNotAuthenticated = true,
        redirectIfNotVerified = true,
        ...options
    }: LarafetchOptions<R> = {}
) {
    const backendUrl = process.env.BACKEND_URL;

    const frontendUrl = process.env.FRONTEND_URL;


    await initCsrf(backendUrl);
    const    token = getCookie(CSRF_COOKIE);


    let headers: any = {
        ...options?.headers,
        ...(token && { [CSRF_HEADER]: token }),
        accept: "application/json",
    };

    try {
        return await $fetch<T, R>(path, {
            baseURL: backendUrl,
            ...options,
            headers,
            credentials: "include",
        });
    } catch (error) {
        if (!(error instanceof FetchError)) throw error;

        // when any of the following redirects occur and the final throw is not catched then nuxt SSR will log the following error:
        // [unhandledRejection] Error [ERR_HTTP_HEADERS_SENT]: Cannot set headers after they are sent to the client

        if (
            redirectIfNotAuthenticated &&
            [401, 419].includes(error?.response?.status)
        ) {
           window.location.href = "/login";
        }

        if (redirectIfNotVerified && [409].includes(error?.response?.status)) {
            window.location.href = "/verify-email";
        }

        throw error;
    }
}

async function initCsrf(backendUrl) {
    await $fetch("/sanctum/csrf-cookie", {
        baseURL: backendUrl,
        credentials: "include",
    });
}

// https://github.com/axios/axios/blob/bdf493cf8b84eb3e3440e72d5725ba0f138e0451/lib/helpers/cookies.js
function getCookie(name: string) {
    const match = document.cookie.match(
        new RegExp("(^|;\\s*)(" + name + ")=([^;]*)")
    );
    return match ? decodeURIComponent(match[3]) : null;
}
