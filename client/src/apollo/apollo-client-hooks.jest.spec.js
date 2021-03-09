import { beforeEachRequiresAuth, beforeEachRequiresRoles } from './apollo-client-hooks';
import * as Q from "quasar";

jest.mock('quasar', () => ({
    SessionStorage: {
        set: jest.fn()
    }
}));
const setSession = Q.SessionStorage.set;

const apolloMock = {
    query: jest.fn()
}

describe('Router RequireAuth hook', () => {
    afterEach(() => {
        apolloMock.query.mockClear();
        Q.SessionStorage.set.mockClear();
    })

    it('does nothing when logged in', async () => {
        const to = {
            matched: [{ meta: { requiresAuth: true } }]
        };

        apolloMock.query.mockResolvedValue({
            data: {
                currentUser: {
                    id: 1
                }
            }
        });

        const next = jest.fn();

        await beforeEachRequiresAuth(apolloMock, to, undefined, next);

        expect(apolloMock.query).toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).toBe(undefined);
    });

    it('redirects when not logged in', async () => {
        const to = {
            matched: [{ meta: { requiresAuth: true } }]
        };

        apolloMock.query.mockResolvedValue({
            data: {
                currentUser: null
            }
        });

        const next = jest.fn();

        await beforeEachRequiresAuth(apolloMock, to, undefined, next);

        expect(apolloMock.query).toHaveBeenCalled();
        expect(setSession).toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).not.toBe(undefined);
    });

    it('does nothing it route does not require auth', async () => {
        const to = {
            matched: [{ meta: {} }]
        };

        const next = jest.fn();

        await beforeEachRequiresAuth(apolloMock, to, undefined, next);

        expect(apolloMock.query).not.toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).toBe(undefined);
    });
});

describe('Router RequireRoles hook', () => {
    afterEach(() => {
        apolloMock.query.mockClear();
    })

    it('does nothing when user has role', async () => {
        const to = {
            matched: [{ meta: { requiresRoles: 'admin' } }]
        };

        apolloMock.query.mockResolvedValue({
            data: {
                currentUser: {
                    id: 1,
                    roles: [
                        { name: 'admin'}
                    ]
                }
            }
        });

        const next = jest.fn();

        await beforeEachRequiresRoles(apolloMock, to, undefined, next);

        expect(apolloMock.query).toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).toBe(undefined);
    });

    it('redirects when user does not have role', async () => {
        const to = {
            matched: [{ meta: { requiresRoles: 'admin' } }]
        };

        apolloMock.query.mockResolvedValue({
            data: {
                currentUser: {
                    id: 1,
                    roles: [

                    ]
                }
            }
        });

        const next = jest.fn();

        await beforeEachRequiresRoles(apolloMock, to, undefined, next);

        expect(apolloMock.query).toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).not.toBe(undefined);
    });

    it('does nothing is requireRoles is not present', async () => {
        const to = {
            matched: [{ meta: {} }]
        };

        const next = jest.fn();

        await beforeEachRequiresRoles(apolloMock, to, undefined, next);

        expect(apolloMock.query).not.toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).toBe(undefined);
    });

    it('requires all nested roles', async () => {
        const to = {
            matched: [{ meta: { requiresRoles: 'admin' } }, {meta: { requiresRoles: 'specialAdmin' } }]
        };

        apolloMock.query.mockResolvedValue({
            data: {
                currentUser: {
                    id: 1,
                    roles: [
                        { name: 'admin' }
                    ]
                }
            }
        });
        const next = jest.fn();
        await beforeEachRequiresRoles(apolloMock, to, undefined, next);

        expect(apolloMock.query).toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).not.toBe(undefined);
    });


    it('passes if user has all nested roles', async () => {
        const to = {
            matched: [{ meta: { requiresRoles: 'admin' } }, {meta: { requiresRoles: 'specialAdmin' } }]
        };

        apolloMock.query.mockResolvedValue({
            data: {
                currentUser: {
                    id: 1,
                    roles: [
                        { name: 'admin' },
                        { name: 'specialAdmin'}
                    ]
                }
            }
        });
        const next = jest.fn();
        await beforeEachRequiresRoles(apolloMock, to, undefined, next);

        expect(apolloMock.query).toHaveBeenCalled();
        expect(next).toHaveBeenCalled();
        expect(next.mock.calls[0][0]).toBe(undefined);
    });
});