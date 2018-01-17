<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Http\Header;

/**
 * Description of HttpHeaders
 *
 * @author guillaume
 */
abstract class HttpHeaders {

    const headers = [
        'A-IM', //	http		[RFC4229]
        'Accept', //	http	standard	[RFC7231, Section 5.3.2]
        'Accept-Additions', //	http		[RFC4229]
        'Accept-Charset', //	http	standard	[RFC7231, Section 5.3.3]
        'Accept-Datetime', //	http	informational	[RFC7089]
        'Accept-Encoding', //	http	standard	[RFC7231, Section 5.3.4][RFC7694, Section 3]
        'Accept-Features', //	http		[RFC4229]
        'Accept-Language', //	http	standard	[RFC7231, Section 5.3.5]
        'Accept-Patch', //	http		[RFC5789]
        'Accept-Post', //perm/accept-post	http	standard	[https://www.w3.org/TR/ldp/]
        'Accept-Ranges', //	http	standard	[RFC7233, Section 2.3]
        'Age', //	http	standard	[RFC7234, Section 5.1]
        'Allow', //	http	standard	[RFC7231, Section 7.4.1]
        'ALPN', //	http	standard	[RFC7639, Section 2]
        'Alt-Svc', //	http	standard	[RFC7838]
        'Alt-Used', //	http	standard	[RFC7838]
        'Alternates', //	http		[RFC4229]
        'Apply-To-Redirect-Ref', //	http		[RFC4437]
        'Authentication-Control', //	http	experimental	[RFC8053, Section 4]
        'Authentication-Info', //	http	standard	[RFC7615, Section 3]
        'Authorization', //	http	standard	[RFC7235, Section 4.2]
        'Body', //	none	reserved	[RFC6068]
        'C-Ext', //	http		[RFC4229]
        'C-Man', //	http		[RFC4229]
        'C-Opt', //	http		[RFC4229]
        'C-PEP', //	http		[RFC4229]
        'C-PEP-Info', //	http		[RFC4229]
        'Cache-Control', //	http	standard	[RFC7234, Section 5.2]
        'CalDAV-Timezones', //	http	standard	[RFC7809, Section 7.1]
        'Close', //	http	reserved	[RFC7230, Section 8.1]
        'Connection', //	http	standard	[RFC7230, Section 6.1]
        'Content-Alternative', //	MIME		[RFC4021]
        'Content-Description', //	MIME		[RFC4021]
        'Content-Disposition', //	http	standard	[RFC6266]
        'Content-Duration', //	MIME		[RFC4021]
        'Content-Encoding', //	http	standard	[RFC7231, Section 3.1.2.2]
        'Content-features', //	MIME		[RFC4021]
        'Content-ID', //	http		[RFC4229]
        'Content-Language', //	http	standard	[RFC7231, Section 3.1.3.2]
        'Content-Length', //	http	standard	[RFC7230, Section 3.3.2]
        'Content-Location', //	http	standard	[RFC7231, Section 3.1.4.2]
        'Content-Location', //	MIME		[RFC4021]
        'Content-MD5', //	http		[RFC4229]
        'Content-Range', //	http	standard	[RFC7233, Section 4.2]
        'Content-Script-Type', //	http		[RFC4229]
        'Content-Style-Type', //	http		[RFC4229]
        'Content-Transfer-Encoding', //	MIME		[RFC4021]
        'Content-Translation-Type', //	MIME	standard	[RFC8255]
        'Content-Type', //	http	standard	[RFC7231, Section 3.1.1.5]
        'Content-Version', //	http		[RFC4229]
        'Cookie', //	http	standard	[RFC6265]
        'DASL', //	http	standard	[RFC5323]
        'DAV', //	http	standard	[RFC4918]
        'Date', //	http	standard	[RFC7231, Section 7.1.1.2]
        'Default-Style', //	http		[RFC4229]
        'Delta-Base', //	http		[RFC4229]
        'Depth', //	http	standard	[RFC4918]
        'Derived-From', //	http		[RFC4229]
        'Destination', //	http	standard	[RFC4918]
        'Differential-ID', //	http		[RFC4229]
        'Digest', //	http		[RFC4229]
        'ETag', //	http	standard	[RFC7232, Section 2.3]
        'Expect', //	http	standard	[RFC7231, Section 5.1.1]
        'Expires', //	http	standard	[RFC7234, Section 5.3]
        'Ext', //	http		[RFC4229]
        'Forwarded', //	http	standard	[RFC7239]
        'From', //	http	standard	[RFC7231, Section 5.5.1]
        'GetProfile', //	http		[RFC4229]
        'Hobareg', //	http	experimental	[RFC7486, Section 6.1.1]
        'Host', //	http	standard	[RFC7230, Section 5.4]
        'HTTP2-Settings', //	http	standard	[RFC7540, Section 3.2.1]
        'IM', //	http		[RFC4229]
        'If', //	http	standard	[RFC4918]
        'If-Match', //	http	standard	[RFC7232, Section 3.1]
        'If-Modified-Since', //	http	standard	[RFC7232, Section 3.3]
        'If-None-Match', //	http	standard	[RFC7232, Section 3.2]
        'If-Range', //	http	standard	[RFC7233, Section 3.2]
        'If-Schedule-Tag-Match', //	http	standard	[RFC6638]
        'If-Unmodified-Since', //	http	standard	[RFC7232, Section 3.4]
        'Keep-Alive', //	http		[RFC4229]
        'Label', //	http		[RFC4229]
        'Last-Modified', //	http	standard	[RFC7232, Section 2.2]
        'Link', //	http	standard	[RFC-nottingham-rfc5988bis-08]
        'Location', //	http	standard	[RFC7231, Section 7.1.2]
        'Lock-Token', //	http	standard	[RFC4918]
        'Man', //	http		[RFC4229]
        'Max-Forwards', //	http	standard	[RFC7231, Section 5.1.2]
        'Memento-Datetime', //	http	informational	[RFC7089]
        'Meter', //	http		[RFC4229]
        'MIME-Version', //	http	standard	[RFC7231, Appendix A.1]
        'MIME-Version', //	MIME		[RFC4021]
        'Negotiate', //	http		[RFC4229]
        'Opt', //	http		[RFC4229]
        'Optional-WWW-Authenticate', //	http	experimental	[RFC8053, Section 3]
        'Ordering-Type', //	http	standard	[RFC4229]
        'Origin', //	http	standard	[RFC6454]
        'Overwrite', //	http	standard	[RFC4918]
        'P3P', //	http		[RFC4229]
        'PEP', //	http		[RFC4229]
        'PICS-Label', //	http		[RFC4229]
        'Pep-Info', //	http		[RFC4229]
        'Position', //	http	standard	[RFC4229]
        'Pragma', //	http	standard	[RFC7234, Section 5.4]
        'Prefer', //	http	standard	[RFC7240]
        'Preference-Applied', //	http	standard	[RFC7240]
        'ProfileObject', //	http		[RFC4229]
        'Protocol', //	http		[RFC4229]
        'Protocol-Info', //	http		[RFC4229]
        'Protocol-Query', //	http		[RFC4229]
        'Protocol-Request', //	http		[RFC4229]
        'Proxy-Authenticate', //	http	standard	[RFC7235, Section 4.3]
        'Proxy-Authentication-Info', //	http	standard	[RFC7615, Section 4]
        'Proxy-Authorization', //	http	standard	[RFC7235, Section 4.4]
        'Proxy-Features', //	http		[RFC4229]
        'Proxy-Instruction', //	http		[RFC4229]
        'Public', //	http		[RFC4229]
        'Public-Key-Pins', //	http	standard	[RFC7469]
        'Public-Key-Pins-Report-Only', //	http	standard	[RFC7469]
        'Range', //	http	standard	[RFC7233, Section 3.1]
        'Redirect-Ref', //	http		[RFC4437]
        'Referer', //	http	standard	[RFC7231, Section 5.5.2]
        'Retry-After', //	http	standard	[RFC7231, Section 7.1.3]
        'Safe', //	http		[RFC4229]
        'Schedule-Reply', //	http	standard	[RFC6638]
        'Schedule-Tag', //	http	standard	[RFC6638]
        'Sec-WebSocket-Accept', //	http	standard	[RFC6455]
        'Sec-WebSocket-Extensions', //	http	standard	[RFC6455]
        'Sec-WebSocket-Key', //	http	standard	[RFC6455]
        'Sec-WebSocket-Protocol', //	http	standard	[RFC6455]
        'Sec-WebSocket-Version', //	http	standard	[RFC6455]
        'Security-Scheme', //	http		[RFC4229]
        'Server', //	http	standard	[RFC7231, Section 7.4.2]
        'Set-Cookie', //	http	standard	[RFC6265]
        'SetProfile', //	http		[RFC4229]
        'SLUG', //	http	standard	[RFC5023]
        'SoapAction', //	http		[RFC4229]
        'Status-URI', //	http		[RFC4229]
        'Strict-Transport-Security', //	http	standard	[RFC6797]
        'Surrogate-Capability', //	http		[RFC4229]
        'Surrogate-Control', //	http		[RFC4229]
        'TCN', //	http		[RFC4229]
        'TE', //	http	standard	[RFC7230, Section 4.3]
        'Timeout', //	http	standard	[RFC4918]
        'Topic', //	http	standard	[RFC8030, Section 5.4]
        'Trailer', //	http	standard	[RFC7230, Section 4.4]
        'Transfer-Encoding', //	http	standard	[RFC7230, Section 3.3.1]
        'TTL', //	http	standard	[RFC8030, Section 5.2]
        'Urgency', //	http	standard	[RFC8030, Section 5.3]
        'URI', //	http		[RFC4229]
        'Upgrade', //	http	standard	[RFC7230, Section 6.7]
        'User-Agent', //	http	standard	[RFC7231, Section 5.5.3]
        'Variant-Vary', //	http		[RFC4229]
        'Vary', //	http	standard	[RFC7231, Section 7.1.4]
        'Via', //	http	standard	[RFC7230, Section 5.7.1]
        'WWW-Authenticate', //	http	standard	[RFC7235, Section 4.1]
        'Want-Digest', //	http		[RFC4229]
        'Warning', //	http	standard	[RFC7234, Section 5.5]
        'X-Content-Type-Options', //	http	standard	[https://fetch.spec.whatwg.org/#x-content-type-options-header]
        'X-Frame-Options', //	http	informational	[RFC7034]
    ];

    private function __construct() {
        
    }

}
