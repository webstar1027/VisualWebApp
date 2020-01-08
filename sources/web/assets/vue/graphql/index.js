import Vue from 'vue';
import VueApollo from 'vue-apollo';
import ApolloClient from 'apollo-client';
import { HttpLink } from 'apollo-link-http';
import { onError } from 'apollo-link-error';
import { InMemoryCache } from 'apollo-cache-inmemory';

Vue.use(VueApollo);

const httpLink = new HttpLink({
    uri: '/graphql',
});

const onErrorLink = onError(({ graphQLErrors, networkError }) => {
    if (graphQLErrors) {
        graphQLErrors.map(({ message, locations, path }) =>
            console.log(
                `[GraphQL error]: Message: ${message}, Location: ${locations}, Path: ${path}`,
            ),
        );
    }
    if (networkError) {
        if (networkError.statusCode === 500) {
            document.open();
            document.write(networkError.response.status + ' ' + networkError.response.statusText);
            document.close();
        } else if (networkError.statusCode === 401) {
            window.location.href = '/api/security/logout';
        }
    }
});

const cache = new InMemoryCache();

const apolloClient = new ApolloClient({
    link: onErrorLink.concat(httpLink),
    cache,
});

export default new VueApollo({
    defaultClient: apolloClient,
});
