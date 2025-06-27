# Serving Front-End Assets from a CDN

Available Since: preview (This feature isn't available in a released version yet.)

Serving front-end assets (JavaScript bundles, CSS files, and default images) from a Content Delivery Network (CDN) can help with scalability and performance especially when dealing with a large request volume.

Pilcrow will rewrite asset requests to point to the CDN server.

::: warning NOTE
Configuring a CDN is not for the faint of heart.  CORS problems, crossorigin issues and cache busting are all things that have to be addressed for a production deployment.  For most self-hosted deployments, configuring a CDN is almost certainly not worth the effort.
:::

## Configure CDN
You'll need to upload the client bundle files to your CDN of choice.  This example uses [Digitalocean Spaces](https://docs.digitalocean.com/products/spaces/how-to/enable-cdn/), but any push-type CDN will work similarly.  We're only covering the steps needed to configure the application.  You'll need to configure the CDN itself.

### 1. Build the front-end assets
```bash
yarn #Install build dependencies
yarn build #Build the client application
```

### 2. Push to CDN
Once the build finishes, the bundled application files will be located in the `dist/spa` folder.  We need to copy these files to our CDN.  For Digitalocean, we upload the files to a spaces bucket.


```bash
s3cmd -Pr --no-mime-magic --guess-mime-type dist/spa s3://pilcrow-cdn

```
The `-P` flag sets the ACL to public.  The needed flags may be different for your CDN setup.

### 3. Configure app to use CDN
Update your environment to set the `CDN_BASE` environment var to the URL of your CDN.

```env
#inside .env
CDN_BASE=https://cdn.pilcrow.dev/  #Set this to your CDN base url.
```
If using docker-compose, restart the application with the new configuration:

```bash
docker-compose up -d
```
Since the `index.html` template is cached, you will likely need to update the view cache.

```bash
docker-compose exec phpfpm ./artisan view:cache
```

### 4. Confirm
Use the browser's inspector to confirm that front-end files are being loaded from the CDN rather than locally.

<CaptionImage src="images/inspector.png" caption="Chrome inspector network tab"/>

