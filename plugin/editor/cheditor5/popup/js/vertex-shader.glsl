// -------------------------------------------------------
// CHEditor WebGL Vertex Shader
// -------------------------------------------------------
attribute vec2 a_position;
uniform vec2 u_resolution;

void main() {
    vec2 clipSpace = (a_position / u_resolution * 2.0) - 1.0;
    gl_Position = vec4(clipSpace * vec2(1, -1), 0, 1);
}
