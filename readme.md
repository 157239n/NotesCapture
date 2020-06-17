
Future directions:
- Make the default icons more diverse, using these avatar sets:
    - https://www.flaticon.com/packs/avatar-set
    - https://www.flaticon.com/packs/people-avatar-collection
    - https://www.flaticon.com/packs/people-avatar-set
    - https://www.flaticon.com/packs/user-avatar-collection
- Implement CSRF tokens for all requests, with each user have their own token, and it cycles every 10 minutes using a cron job. Then accepts a request only for the last 2 tokens.
- Try pass through POST requests
- Make the card so that if they are too close to each other, they will have a force pushing them out, so they won't overlap. A simpler solution might be to allow them to overlap each other, but at least randomizes their z indexes in a range, so they can be clearly seen.
