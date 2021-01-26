// -------------------------------------------------------
// CHEditor WebGL Fragment Shader
// -------------------------------------------------------
precision highp float;

uniform sampler2D u_image;
uniform vec2 u_resolution;
uniform vec2 u_srcResolution;

vec4 getTextureColor(vec2 loc) {
    return texture2D(u_image, loc / u_srcResolution);
}

void main() {
    vec2 ratio = u_srcResolution / u_resolution;
    vec2 ratioHalf = ceil(ratio / 1.0);
    vec2 loc = gl_FragCoord.xy;

    loc.y = u_resolution.y - loc.y;

    float weight = 0.0;
    float weights = 0.0;
    float weights_alpha = 0.0;
    vec3 gx_rgb = vec3(0.0);
    float gx_a = 0.0;
    float center_y = (loc.y + 0.5) * ratio.y;

    float y = floor(loc.y * ratio.y);
    float y_length = (loc.y + 1.0) * ratio.y;

    for (int i = 0; i < 5000; i++) {
        if (y >= y_length) {
           break;
        }
        float dy = abs(center_y - (y + 0.5)) / ratioHalf.y;
        float center_x = (loc.x + 0.5) * ratio.x;
        float part_w = dy * dy;
        float x = floor(loc.x * ratio.x);
        float x_length = (loc.x + 1.0) * ratio.x;

        for (int j = 0; j < 5000; j++) {
            if (x >= x_length) {
                break;
            }

            float dx = abs(center_x - (x + 0.5)) / ratioHalf.x;
            float w = sqrt(part_w + dx * dx);

            if (w >= -1.0 && w <= 1.0) {
                // Hermite 필터
                weight = 2.0 * w * w * w - 3.0 * w * w + 1.0;
                if (weight > 0.0) {
                    vec4 pixel = getTextureColor(vec2(x, y)) * 255.0;

                    // 알파 채널
                    gx_a += weight * pixel.a;
                    weights_alpha += weight;

                    if (pixel.a < 255.0) {
                        weight = weight * pixel.a / 250.0;
                    }
                    gx_rgb += weight * pixel.rgb;
                    weights += weight;
                }
            }
            x++;
        }
        y++;
    }

    gx_rgb = (gx_rgb / weights) / 255.0;
    gx_a = (gx_a / weights_alpha) / 255.0;

    gl_FragColor = vec4(gx_rgb, gx_a);
}
